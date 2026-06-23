#ifndef _COMPONENT_PLAYER_H_
#define _COMPONENT_PLAYER_H_
#include "EntityPlayer.h"
#include "sprite.h"

#define GRAVITY 1
struct _ComponentPlayer
{
    void (*Initialize)(Entity_ECS *, u16 x, u16 y);
    void (*Moviment)(Entity_ECS *, bool);
    TypeEntity (*CollideSelf)(Entity_ECS *player, PointData *position);
    void (*Destroy)(Entity_ECS *);
};
extern struct _ComponentPlayer ComponentPlayer;
#endif
