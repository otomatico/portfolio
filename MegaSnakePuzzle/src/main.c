#include <genesis.h>

#include "game.h"

void init(StateGame *);
void update(StateGame *, World_ECS *);
void draw(StateGame *, World_ECS *);
Input key_pressed = NONE;
int main()
{
    World_ECS world;
    StateGame stateGame;
    init(&stateGame);
    do
    {
        if (key_pressed == NONE)
        {
            key_pressed = Game_Input(JOY_1);
        }
        update(&stateGame, &world);
        draw(&stateGame, &world);
        SPR_update();
        SYS_doVBlankProcess();
    } while (1);
    return (0);
}

void init(StateGame *state)
{
    // Set the default width screen to 320px
    VDP_setScreenWidth320();
    JOY_init();
    SPR_init();
    *state = STATE_OPEN_INIT;
    frameCounter = 0;
    currentLevel = 0;
}

void update(StateGame *state, World_ECS *world)
{
    switch (*state)
    {
    case STATE_OPEN_INIT:
        Logo_Initialize();
        break;
    case STATE_OPEN:
        Logo_Update(state);
        break;
    case STATE_GAME_INIT:
        Game_Initialize(world, state);
        break;
    case STATE_GAME:
        frameCounter = (frameCounter + 1) % 15;
        if (frameCounter == 0)
        {
            Game_Loop(world, state, key_pressed);
            key_pressed = NONE;
        }
        break;
    case STATE_OVER:
        Game_Reset_Level(world);
        break;
    default:
        *state = STATE_OPEN_INIT;
        break;
    }
}

void draw(StateGame *state, World_ECS *world)
{
    switch (*state)
    {
    case STATE_OPEN_INIT:
        Logo_Draw_Initialize(state);
        break;
    case STATE_OPEN:
        Logo_Draw(state);
        break;
    case STATE_GAME_INIT:
        *state = STATE_GAME;
        break;
    case STATE_GAME:
        Game_Draw(world);
        break;
    case STATE_OVER:
        Game_Over(state);
        break;
    default:
        *state = STATE_OPEN_INIT;
        break;
    }
}