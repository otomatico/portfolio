#ifndef _ENTITY_MAP_H_
#define _ENTITY_MAP_H_

#include "EntityPlayer.h"

typedef enum
{
    MAP_POINT,
    MAP_LINE_HORIZONTAL,
    MAP_LINE_VERTICAL
} TypeDrawMap;

typedef struct
{
    TypeDrawMap draw;
    TypeEntity entity;
    int x;
    int y;
    int lenght;
} EntityDrawMap;

typedef struct
{
    int lenght;
    EntityDrawMap *tiles;
} EntityMap;
#endif