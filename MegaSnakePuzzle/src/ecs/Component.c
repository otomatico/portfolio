#ifndef _COMPONENTS_C_
#define _COMPONENTS_C_

#include "ecs/Component.h"

struct _Component Component = {
    .world = &ComponentWorld,
    .player = &ComponentPlayer
    //    .map = &ComponentMap
};

#endif