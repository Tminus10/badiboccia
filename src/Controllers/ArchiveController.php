<?php

final class ArchiveController
{
    public static function index(array $params): void
    {
        $seasons = array_filter(Season::all(), fn ($s) => (int) $s['is_current'] !== 1 && !Season::isUpcoming($s));
        render('archive', [
            'seasons' => array_values($seasons),
        ]);
    }
}
