#ifndef _MAP_TILES_H_
#define _MAP_TILES_H_

#include "ecs/EntityMap.h"

// Es una array de punteros que apuntan a los datos estaticos
#ifndef MAX_TILES
#define MAX_TILES 4
#endif
extern const EntityMap *Tiles[MAX_TILES];

#endif