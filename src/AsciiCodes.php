<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * AsciiCodes is a container class with escape sequences for foreground and background
 * colors, bold, underline, and blink effects, and their respective reset codes.
 */
namespace Onesimus\Logger;

class AsciiCodes
{
    // Resets
    const RESET_ALL       = "\033[0m";
    const RESET_BOLD      = "\033[22m";
    const RESET_UNDERLINE = "\033[24m";
    const RESET_BLINK     = "\033[25m";
    const RESET_FG_COLOR  = "\033[22;39m";
    const RESET_BG_COLOR  = "\033[49m";

    // Effects
    const EFFECT_BOLD      = "\033[1m";
    const EFFECT_UNDERLINE = "\033[4m";
    const EFFECT_BLINK     = "\033[5m";

    // Foreground Colors normal intensity
    const FG_COLOR_BLACK   = "\033[30m";
    const FG_COLOR_RED     = "\033[31m";
    const FG_COLOR_GREEN   = "\033[32m";
    const FG_COLOR_BROWN   = "\033[33m";
    const FG_COLOR_BLUE    = "\033[34m";
    const FG_COLOR_MAGENTA = "\033[35m";
    const FG_COLOR_CYAN    = "\033[36m";
    const FG_COLOR_GRAY    = "\033[37m";

    // Foreground Colors bold/increased intensity
    const FG_COLOR_DARK_GRAY     = "\033[1;30m";
    const FG_COLOR_LIGHT_RED     = "\033[1;31m";
    const FG_COLOR_LIGHT_GREEN   = "\033[1;32m";
    const FG_COLOR_YELLOW        = "\033[1;33m";
    const FG_COLOR_LIGHT_BLUE    = "\033[1;34m";
    const FG_COLOR_LIGHT_MAGENTA = "\033[1;35m";
    const FG_COLOR_LIGHT_CYAN    = "\033[1;36m";
    const FG_COLOR_WHITE         = "\033[1;37m";

    // Foreground Color Aliases
    const FG_COLOR_PURPLE = self::FG_COLOR_MAGENTA;
    const FG_COLOR_GREY   = self::FG_COLOR_GRAY;
    const FG_COLOR_ORANGE = self::FG_COLOR_BROWN;
    const FG_COLOR_LIGHT_PURPLE = self::FG_COLOR_LIGHT_MAGENTA;
    const FG_COLOR_DARK_GREY    = self::FG_COLOR_DARK_GRAY;

    // Background Colors
    const BG_COLOR_BLACK   = "\033[40m";
    const BG_COLOR_RED     = "\033[41m";
    const BG_COLOR_GREEN   = "\033[42m";
    const BG_COLOR_YELLOW  = "\033[43m";
    const BG_COLOR_BLUE    = "\033[44m";
    const BG_COLOR_MAGENTA = "\033[45m";
    const BG_COLOR_CYAN    = "\033[46m";
    const BG_COLOR_WHITE   = "\033[47m";
}
