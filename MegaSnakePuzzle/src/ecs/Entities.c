#ifndef _ENTITY_C_
#define _ENTITY_C_
#include "ecs/Entities.h"
// Comparador de puntos
bool EqualPoint(PointData *a, PointData *b)
{
    return a->x == b->x && a->y == b->y;
}
// Destruir entidad
void DestroyEntity(Entity_ECS *e)
{
    if (!e)
        return;
    free(e->data);
    free(e);
}
#endif
