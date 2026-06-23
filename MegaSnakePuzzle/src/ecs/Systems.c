#ifndef _SYSTEMS_C_
#define _SYSTEMS_C_

#include "ecs/System.h"
// Suelo limite 224/8 = 28 Sprites 8x8
#define GROUND 28

static inline void InputSystem(Entity_ECS *player, Input key)
{
    switch (key)
    {
    case LEFT:
        player->vel.dx = -1;
        break;
    case RIGHT:
        player->vel.dx = 1;
        break;
    case UP:
        player->vel.dy = -1;
        break;
    case DOWN:
        player->vel.dy = 1;
        break;
    }
}

static inline void PhysicsSystem(World_ECS *w)
{
    Entity_ECS *player = w->player;
    SnakeData *snake = (SnakeData *)player->data;
    // Verificar si alguna parte está apoyada
    bool supported = false;
    PointData position;
    // Esta soportado por algun elemento
    for (int index = 0; index < snake->length && !supported; index++)
    {
        position.x = snake->body[index].x;
        position.y = snake->body[index].y + 1;
        supported = (Component.world->Collide(w, &position) != ENTITY_NONE);
    }

    PointData head = (PointData){snake->body[0].x + player->vel.dx, snake->body[0].y + player->vel.dy, NULL};
    // Evite subir sobre si mismo o traspasar paredes
    TypeEntity entity = Component.world->Collide(w, &head);
    // Si la posicion esta Ocupada
    if (Component.player->CollideSelf(player, &head) == ENTITY_SNAKE)
    {
        player->vel.dx = 0;
        player->vel.dy = 0;
    }
    if (entity != ENTITY_PLATFORM && entity != ENTITY_ROCK)
    {
        Component.player->Moviment(player, supported);
    }
    // Reset de velocidades temporales
    player->vel.dx = 0;
    player->vel.dy = 0;
}

static inline bool CollisionSystem(World_ECS *w)
{
    SnakeData *snake = (SnakeData *)w->player->data;
    PointData *head = &snake->body[0];

    // Caída al vacío
    if (head->y > GROUND)
    {
        return true;
    }
    TypeEntity hit = Component.world->Collide(w, head);
    switch (hit)
    {
    case ENTITY_EXIT:
        Component.world->Destroy(w);
        Component.world->Load(w, w->id + 1);
        break;
    case ENTITY_FOOD:
        Component.world->EnabledEntity(w, head, 0);
        snake->length++;
        break;
    default:
        break;
    }
    return false;
}

static inline RenderMap(Entity_ECS *entities[], int count)
{
    PointData *p = NULL;
    for (int index = 0; index < count; index++)
    {
        p = ((PointData *)entities[index]->data);

        if (entities[index]->active)
        {
            SPR_setPosition(p->sprite, p->x * 8, p->y * 8);
        }
        else if (SPR_isVisible(p->sprite, 0))
        {
            SPR_setVisibility(p->sprite, HIDDEN);
        }
    }
}

static inline void RenderPlayer(SnakeData *sd)
{
    PointData *p = NULL;
    bool flip = false, finalList = false;

    for (int index = 0; index < sd->length; index++)
    {
        p = (sd->body + index);
        if (p->sprite == NULL)
        {
            p->sprite = SPR_addSprite(&sTail, p->x, p->y, TILE_ATTR(PAL1, FALSE, FALSE, FALSE));
            SPR_setDefinition(sd->body[index - 1].sprite, &sBody);
        }

        finalList = (index == sd->length - 1);
        flip = (p->y != sd->body[index + (finalList ? -1 : 1)].y);

        // Coge el siguiente segmento y gira 90 grados
        SPR_setFrame(p->sprite, flip ? 1 : 0);

        flip = false;
        if (p->x != sd->body[index + (finalList ? -1 : 1)].x)
        {
            flip = finalList ? sd->body[index - 1].x < p->x : p->x < sd->body[index + 1].x;
        }
        SPR_setHFlip(p->sprite, flip);

        flip = false;
        if (p->y != sd->body[index + (finalList ? -1 : 1)].y)
        {
            flip = finalList ? sd->body[index - 1].y > p->y : p->y > sd->body[index + 1].y;
        }
        SPR_setVFlip(p->sprite, flip);

        SPR_setPosition(p->sprite, p->x * 8, p->y * 8);
    }
}

static inline void RenderSystem(World_ECS *w)
{
    RenderMap(w->entities, w->count);
    RenderPlayer((SnakeData *)w->player->data);
}

struct _System System = {
    .WatchGamePad = InputSystem,
    .Physics = PhysicsSystem,
    .Collide = CollisionSystem,
    .Render = RenderSystem};

#endif