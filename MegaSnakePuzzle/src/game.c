#include "game.h"
// void inputHandle(u16, u16, u16);
//  Cargar variables para el juego
void Game_Initialize(World_ECS *w, StateGame *state)
{
    //  Inicializar el mundo
    Component.world->Initialize(w);
    // Cargar el mapa
    Component.world->Load(w, 0);
    // Cargar los sprites del juego
    Game_Load(w);
}

// Cargar los sprites necesarios para el juego
void Game_Load_Sprite_Map(Entity_ECS *entities[], int count)
{
    SpriteDefinition *pivot = NULL;
    PointData *p = NULL;
    int attr = TILE_ATTR(PAL1, FALSE, FALSE, FALSE);

    for (int index = 0; index < count; index++)
    {
        p = (PointData *)entities[index]->data;
        if (entities[index]->type == ENTITY_SPIKE)
            pivot = &sSpike;
        if (entities[index]->type == ENTITY_ROCK)
            pivot = &sRock;
        if (entities[index]->type == ENTITY_FOOD)
            pivot = &sFood;
        if (entities[index]->type == ENTITY_PLATFORM)
            pivot = &sPlataform;
        if (entities[index]->type == ENTITY_EXIT)
            pivot = &sDoor;
        p->sprite = SPR_addSprite(pivot, p->x * 8, p->y * 8, attr);
        SPR_setVisibility(p->sprite, entities[index]->active ? VISIBLE : HIDDEN);
    }
}

void Game_Load_Sprite_Player(SnakeData *sd)
{
    SpriteDefinition *pivot = NULL;
    PointData *p = NULL;
    int attr = TILE_ATTR(PAL1, FALSE, FALSE, FALSE);

    const int length = sd->length - 1;
    for (int index = 0; index < sd->length; index++)
    {
        p = &(sd->body[index]);
        if (index == 0)
        {
            pivot = &sPlayer;
        }
        else if (index == length)
        {
            pivot = &sTail;
        }
        else
        {
            pivot = &sBody;
        }
        p->sprite = SPR_addSprite(pivot, p->x * 8, p->y * 8, attr);
    }
}

void Game_Load(World_ECS *w)
{
    // Limpiar Pantalla
    SPR_reset();
    VDP_resetSprites();
    VDP_clearPlane(BG_A, TRUE);

    SYS_disableInts();
    u16 indexImage = TILE_USER_INDEX;
    VDP_drawImageEx(BG_A, &bg, TILE_ATTR_FULL(PAL0, FALSE, FALSE, FALSE, indexImage), 0, 0, TRUE, DMA);
    indexImage += bg.tileset->numTile;
    PAL_setPalette(PAL1, pal1.data, CPU);
    SYS_enableInts();

    SpriteDefinition *pivot = NULL;
    PointData *p = NULL;
    int attr = TILE_ATTR(PAL1, FALSE, FALSE, FALSE);

    for (int index = 0; index < w->count; index++)
    {
        Entity_ECS *e = w->entities[index];
        if (!e->active)
            continue;
        p = e->data;
        if (e->type == ENTITY_SPIKE)
            pivot = &sSpike;
        if (e->type == ENTITY_ROCK)
            pivot = &sRock;
        if (e->type == ENTITY_FOOD)
            pivot = &sFood;
        if (e->type == ENTITY_PLATFORM)
            pivot = &sPlataform;
        if (e->type == ENTITY_EXIT)
            pivot = &sDoor;
        p->sprite = SPR_addSprite(pivot, p->x * 8, p->y * 8, attr);
    }

    pivot = &sPlayer;
    SnakeData *sd = (SnakeData *)w->player->data;
    const int length = sd->length - 1;
    for (int index = 0; index < sd->length; index++)
    {
        p = &(sd->body[index]);
        if (index == 0)
        {
            pivot = &sPlayer;
        }
        else if (index == length)
        {
            pivot = &sTail;
        }
        else
        {
            pivot = &sBody;
        }
        p->sprite = SPR_addSprite(pivot, p->x * 8, p->y * 8, attr);
    }
}

Input Game_Input(u16 joy)
{
    // Read Current Joypad Value
    u16 value = JOY_readJoypad(joy);

    if (value & BUTTON_UP)
    {
        return UP;
    }
    // If button DOWN pressed
    if (value & BUTTON_DOWN)
    {
        return DOWN;
    }
    if (value & BUTTON_LEFT)
    {
        return LEFT;
    }
    if (value & BUTTON_RIGHT)
    {
        return RIGHT;
    }
    if (value & BUTTON_A)
    {
        return A;
    }
    if (value & BUTTON_B)
    {
        return B;
    }
    if (value & BUTTON_MODE)
    {
        return SELECT;
    }

    if (value & BUTTON_START)
    {
        return START;
    }

    return NONE; // Return None Otherwise
}

void Game_Loop(World_ECS *world, StateGame *state, Input key)
{
    System.WatchGamePad(world->player, key);
    bool collide = System.Collide(world);
    if (currentLevel != world->id)
    {
        Game_Load(world);
        currentLevel = world->id;
        return;
    }
    if (collide)
    {
        *state = STATE_OVER;
        return;
    }
    System.Physics(world);
}

void Game_Draw(World_ECS *world)
{
    System.Render(world);
}

void Game_Reset_Level(World_ECS *world)
{
    Component.world->Destroy(world);
    currentLevel = 0;
}
void Game_Over(StateGame *state)
{
    SYS_disableInts();
    // Limpiar Pantalla
    SPR_reset();
    VDP_resetSprites();
    VDP_clearPlane(BG_A, TRUE);
    SYS_enableInts();
    *state = STATE_OPEN_INIT;
}
/*
void inputHandle(u16 joy, u16 changed, u16 state)
{
    if (joy == JOY_1)
    {
        // if ((changed & BUTTON_C & state) && !player.isJumping)
        //{
        //     player.isJumping = TRUE;
        //     player.spd.dy = -8;
        // }
        if (changed & BUTTON_UP & state)
        {
            return UP;
        }
        // If button DOWN pressed
        if (changed & BUTTON_DOWN & state)
        {
            return DOWN;
        }
        if (changed & BUTTON_LEFT & state)
        {
            return LEFT;
        }
        if (changed & BUTTON_RIGHT & state)
        {
            return RIGHT;
        }
        if (changed & BUTTON_A & state)
        {
            return A;
        }
        if (changed & BUTTON_B & state)
        {
            return B;
        }
        if (changed & BUTTON_MODE & state)
        {
            return SELECT;
        }

        if (changed & BUTTON_START & state)
        {
            return START;
        }

        return NONE; // Return None Otherwise
    }
}
*/