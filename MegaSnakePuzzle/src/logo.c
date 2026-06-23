#include "game.h"
#include "gfx.h"

void Logo_Initialize()
{
    frameCounter = 0;
}

void Logo_Draw_Initialize(StateGame *state)
{
    // Disable Ints
    SYS_disableInts();
    // Set palette to back
    PAL_setPalette(PAL0, palette_black, DMA);
    // Draw Image
    VDP_drawImageEx(BG_A, &logo, TILE_ATTR_FULL(PAL0, FALSE, FALSE, FALSE, TILE_USER_INDEX), 0, 0, FALSE, DMA);
    // Made a Fade In
    PAL_fadeIn(0, 16, logo.palette->data, 30, false);
    // Enable Ints
    SYS_enableInts();
    // Go to NExt State Logo
    *state = STATE_OPEN;
}

void Logo_Update(StateGame *state)
{
    frameCounter++;
}

void Logo_Draw(StateGame *state)
{
    // If the frameCounter > 240 (4 seconds) Go to Next State and made a FadeOut
    if (frameCounter > 240)
    {
        PAL_fadeOut(0, 16, 30, FALSE);
        *state = STATE_GAME_INIT;
        frameCounter = 0;
    }
}
