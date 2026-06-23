#ifndef _COMPONENTS_H_
#define _COMPONENTS_H_

#include "ComponentPlayer.h"
#include "ComponentWorld.h"
// #include "ComponentMap.h"

// Componentes
struct _Component
{
    struct _ComponentWorld *world;
    struct _ComponentPlayer *player;
    //    struct _ComponentMap *map;
} Component;
extern struct _Component Component;
#endif