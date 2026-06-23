#ifndef _COMPONENT_WORLD_C_
#define _COMPONENT_WORLD_C_
#include "ecs/ComponentWorld.h"

static inline void World_Init(World_ECS *w)
{
    w->id = 0;
    w->count = 0;
    for (int i = 0; i < MAX_ENTITIES; i++)
        w->entities[i] = NULL;
    w->player = NULL;
}

// Crear nueva entidad y devolver índice
static inline int World_CreateEntity(World_ECS *w, TypeEntity type, u16 x, u16 y)
{
    if (type == ENTITY_SNAKE)
    {
        w->player = CreateEntity(type, x, y);
        return 0;
    }
    for (int i = 0; i < MAX_ENTITIES; i++)
    {
        if (w->entities[i] == NULL)
        {
            w->entities[i] = CreateEntity(type, x, y);
            w->count++;
            return i;
        }
    }
    return -1; // no hay espacio
}

static inline void World_EntityEnabledByPoint(World_ECS *w, PointData *point, bool active)
{
    for (int i = 0; i < w->count; i++)
    {
        if (EqualPoint(w->entities[i]->data, point))
        {
            w->entities[i]->active = active;
            return;
        }
    }
}

static inline void World_Destroy(World_ECS *w)
{
    for (int i = 0; i < w->count; i++)
    {
        DestroyEntity(w->entities[i]);
        w->entities[i] = NULL;
    }
    DestroyEntity(w->player);
    w->player = NULL;
    w->count = 0;
}

static inline TypeEntity World_PointCollision(World_ECS *w, PointData *position)
{
    for (int index = 0; index < MAX_ENTITIES; index++)
    {
        Entity_ECS *e = w->entities[index];
        if (!e || !e->active)
            continue;

        PointData *f = (PointData *)e->data;
        if (EqualPoint(f, position))
        {
            return e->type;
        }
    }
    return ENTITY_NONE;
}

static inline void World_MapLoad(World_ECS *w, u8 level)
{
    EntityMap *Map = Tiles[level % MAX_TILES];
    for (int index = 0; index < Map->lenght; index++)
    {
        EntityDrawMap *line = &(Map->tiles[index]);
        char bufferText[50];
        sprintf(bufferText, "Loading Entity %d at (%d,%d) Length %d\n", line->entity, line->x, line->y, line->lenght);

        VDP_drawText(bufferText, 2, 2 + index);
        switch (line->draw)
        {
        case MAP_POINT:
            ComponentWorld.CreateEntity(w, line->entity, line->x, line->y);
            break;
        case MAP_LINE_HORIZONTAL:
            for (int x = 0; x < line->lenght; x++)
            {
                ComponentWorld.CreateEntity(w, line->entity, line->x + x, line->y);
            }
            break;
        case MAP_LINE_VERTICAL:
            for (int y = 0; y < line->lenght; y++)
            {
                ComponentWorld.CreateEntity(w, line->entity, line->x, line->y + y);
            }
            break;
        }
    }
    w->id = level;
}

// Inicialización de la estructura.
struct _ComponentWorld ComponentWorld = {
    .Initialize = World_Init,
    .CreateEntity = World_CreateEntity,
    .Collide = World_PointCollision,
    .EnabledEntity = World_EntityEnabledByPoint,
    .Load = World_MapLoad,
    .Destroy = World_Destroy};
#endif