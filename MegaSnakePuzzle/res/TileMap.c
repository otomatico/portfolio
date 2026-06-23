#ifndef ALL_MAPS_H
#define ALL_MAPS_H

#define MAX_TILES 4
#include "TileMap.h"

static EntityDrawMap level1_tiles[] = {
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 11, 21, 3},
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 7, 24, 10},
    {MAP_POINT, ENTITY_EXIT, 17, 20, 1},
    {MAP_POINT, ENTITY_FOOD, 10, 22, 1},
    {MAP_POINT, ENTITY_SNAKE, 10, 23, 1},
};

static const EntityMap level1 = {5, level1_tiles};

static EntityDrawMap level2_tiles[] = {
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 10, 21, 3},
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 6, 24, 6},
    {MAP_POINT, ENTITY_EXIT, 15, 20, 1},
    {MAP_POINT, ENTITY_SNAKE, 8, 23, 1},
    {MAP_POINT, ENTITY_ROCK, 9, 23, 1},
};

static const EntityMap level2 = {5, level2_tiles};

static EntityDrawMap level3_tiles[] = {
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 8, 21, 3},
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 7, 24, 6},
    {MAP_LINE_VERTICAL, ENTITY_PLATFORM, 12, 21, 3},
    {MAP_POINT, ENTITY_SNAKE, 10, 20, 1},
    {MAP_POINT, ENTITY_EXIT, 15, 20, 1},
    {MAP_POINT, ENTITY_FOOD, 7, 23, 1},
    {MAP_POINT, ENTITY_SPIKE, 11, 23, 1},
};

static const EntityMap level3 = {7, level3_tiles};

static EntityDrawMap level4_tiles[] = {
    {MAP_LINE_HORIZONTAL, ENTITY_PLATFORM, 7, 24, 6},
    {MAP_POINT, ENTITY_EXIT, 12, 20, 1},
    {MAP_POINT, ENTITY_SPIKE, 12, 23, 1},
    {MAP_POINT, ENTITY_SNAKE, 10, 23, 1},
    {MAP_POINT, ENTITY_ROCK, 11, 23, 1},
};

static const EntityMap level4 = {4, level4_tiles};

const EntityMap *Tiles[MAX_TILES] = {&level1, &level2, &level3, &level4};

#endif // ALL_MAPS_H
