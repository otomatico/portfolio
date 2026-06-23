#ifndef _ENTITY_H_
#define _ENTITY_H_
#include <genesis.h>

#ifndef allocate
#define allocate(A) (A *)malloc(sizeof(A))
#define allocateArray(A, B) (A *)malloc(sizeof(A) * ((B > 0) ? (B) : 1))
#endif

#define MAX_ENTITIES 80

#ifndef bool
typedef unsigned char bool;
#endif

typedef struct
{
    s8 dx, dy;
    int activo;
} Velocity;

typedef struct
{
    bool active;
    int type;
    Velocity vel; // opcionalmente obligatoria
    void *data;   // apunta a la estructura concreta
} Entity_ECS;

typedef struct
{
    u8 id;
    u8 count;
    Entity_ECS *player;                 // Extrair Jugador .. reduz complejidad
    Entity_ECS *entities[MAX_ENTITIES]; // OJO Array de punteros
} World_ECS;

typedef struct
{
    u16 x, y;
    Sprite *sprite;
} PointData;

typedef enum _TE TypeEntity; // Esto se declara en EntityPlayer.h

bool EqualPoint(PointData *a, PointData *b);
Entity_ECS *CreateEntity(TypeEntity type, u16 x, u16 y);
void DestroyEntity(Entity_ECS *e);
#endif