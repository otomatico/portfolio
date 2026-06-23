
#ifndef _SYSTEMS_H_
#define _SYSTEMS_H_
#include <genesis.h>
#include "Entities.h"
#include "Component.h"
#include "TileMap.h"
#include "gfx.h"
#include "sprite.h"

//  Constante para GamePAD
typedef enum input
{
    NONE,
    UP,
    RIGHT,
    DOWN,
    LEFT,
    A,
    B,
    C,
    START,
    SELECT
} Input;

struct _System
{
    void (*WatchGamePad)(Entity_ECS *, Input);
    void (*Physics)(World_ECS *);
    bool (*Collide)(World_ECS *);
    void (*Render)(World_ECS *);
    // void (*Destroy)(World_ECS *);
    // void (*Destroy)(Component *);
};
extern struct _System System;
#endif
