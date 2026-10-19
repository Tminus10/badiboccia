<?php

final class PageController
{
    public static function rules(array $params): void
    {
        // One template per language (src/Views/rules/<code>.php), German when there is none yet.
        $template = 'rules/' . I18n::locale();
        render(is_file(__DIR__ . '/../Views/' . $template . '.php') ? $template : 'rules/' . I18n::DEFAULT);
    }
}
