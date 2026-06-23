#ifndef _ENTITY_SNAKE_C_
#define _ENTITY_SNAKE_C_

#include "ecs/EntityPlayer.h"

Entity_ECS *CreateEntity(TypeEntity type, u16 x, u16 y)
{
    Entity_ECS *e = allocate(Entity_ECS);
    e->type = type;
    e->active = 1;

    if (type == ENTITY_SNAKE)
    {
        SnakeData *sd = allocate(SnakeData);
        sd->length = 3;
        for (int i = 0; i < sd->length; i++)
        {
            sd->body[i].x = x - i;
            sd->body[i].y = y;
            sd->body[i].sprite = NULL;
        }
        sd->gravityEnabled = 1;
        e->data = sd;
    }
    else
    {
        PointData *p = allocate(PointData);
        p->x = x;
        p->y = y;
        p->sprite = NULL;
        e->data = p;
    }
    e->vel.dx = 0;
    e->vel.dy = 0;
    return e;
}
#endif
