
#ifndef _COMPONENT_WORLD_H_
#define _COMPONENT_WORLD_H_

#include "EntityMap.h"
#include "TileMap.h"

struct _ComponentWorld
{
    void (*Initialize)(World_ECS *);
    int (*CreateEntity)(World_ECS *, TypeEntity, int, int);
    TypeEntity (*Collide)(World_ECS *, PointData *);
    void (*EnabledEntity)(World_ECS *, PointData *, int);
    void (*Load)(World_ECS *w, u8 level);
    void (*Destroy)(World_ECS *);
};
extern struct _ComponentWorld ComponentWorld;
#endif