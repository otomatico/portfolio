#ifndef _GAME_H_
#define _GAME_H_
#include <genesis.h>
#include "ecs/System.h"
#include "TileMap.h"

typedef enum
{
    STATE_OPEN_INIT,
    STATE_OPEN,
    STATE_MENU_INIT,
    STATE_MENU,
    STATE_GAME_INIT,
    STATE_GAME,
    STATE_PAUSE,
    STATE_OVER,
    STATE_END
} StateGame;

void Logo_Initialize();
void Logo_Draw_Initialize(StateGame *);
void Logo_Update(StateGame *);
void Logo_Draw(StateGame *);

// void Menu_Initialize();
// void Menu_Draw_Initialize(StateGame *);
// void Menu_Update(StateGame *);
// void Menu_Draw();

void Game_Initialize(World_ECS *, StateGame *);
void Game_Load(World_ECS *);
void Game_Loop(World_ECS *, StateGame *, Input);
Input Game_Input(u16 joy);
void Game_Draw(World_ECS *);
void Game_Reset_Level(World_ECS *);
void Game_Over(StateGame *);

u16 frameCounter;
u8 currentLevel;
#endif