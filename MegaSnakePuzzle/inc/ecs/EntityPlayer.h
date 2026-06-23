#ifndef _ENTITY_SNAKE_H_
#define _ENTITY_SNAKE_H_

#include "Entities.h"

typedef enum _TE
{
    ENTITY_NONE,
    ENTITY_SNAKE,    // 🐍 Snake
    ENTITY_FOOD,     // 🍎 Food
    ENTITY_PLATFORM, // 🧱 Wall
    ENTITY_ROCK,     // 🪨 Rock
    ENTITY_SPIKE,    // 🔱 Spike
    ENTITY_EXIT      // 🚪 Door
} TypeEntity;

typedef struct
{
    u8 length;
    bool gravityEnabled;
    PointData body[10]; // Max length 10
} SnakeData;

Entity_ECS *CreateEntity(TypeEntity type, u16 x, u16 y);
#endif
