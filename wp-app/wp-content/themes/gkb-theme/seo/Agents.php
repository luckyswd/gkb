<?php

namespace seo;

/**
 * Декларативная WebMCP-разметка форм для агентов в браузере (по образцу
 * raschetnik.by) и подписи полей формы обратной связи для доступности.
 * Форма связи: агент только заполняет поля, отправляет человек.
 */
class Agents
{
    private const CF7_FIELDS = [
        'your-name' => ['Ваше имя', 'Your name', 'Имя человека, который задаёт вопрос. Спросите у пользователя, не придумывайте.'],
        'your-email' => ['Email для ответа', 'E-mail for the reply', 'Email пользователя, на который ГазКотелБел пришлёт ответ. Только с согласия пользователя.'],
        'your-message' => ['Сообщение', 'Message', 'Вопрос о котлах Житомир: модель, мощность, площадь дома, город — всё, что поможет ответить.'],
    ];

    public static function register(): void
    {
        add_filter('wpcf7_form_additional_atts', [self::class, 'formAtts']);
        add_filter('wpcf7_form_elements', [self::class, 'formElements']);
    }

    public static function formAtts(array $atts): array
    {
        $atts['toolname'] = 'contact_manufacturer';
        $atts['tooldescription'] = 'Заполняет форму обратной связи ООО «ГазКотелБел» — официального представителя котлов Житомир (АТЕМ) в Беларуси: имя, email и вопрос. Агент только заполняет поля — заявку отправляет сам пользователь кнопкой «Отправить». Ответ придёт на указанный email.';
        return $atts;
    }

    public static function formElements(string $html): string
    {
        $en = Context::lang() === 'en';
        foreach (self::CF7_FIELDS as $name => [$ru, $enLabel, $desc]) {
            $html = preg_replace_callback(
                '#<(input|textarea)\b([^>]*\bname="' . preg_quote($name, '#') . '"[^>]*)>#',
                static function ($m) use ($ru, $enLabel, $desc, $en) {
                    $attrs = rtrim($m[2]);
                    $selfClose = str_ends_with($attrs, '/');
                    if ($selfClose) {
                        $attrs = rtrim(substr($attrs, 0, -1));
                    }
                    if (stripos($attrs, 'aria-label=') === false) {
                        $attrs .= ' aria-label="' . esc_attr($en ? $enLabel : $ru) . '"';
                    }
                    if (stripos($attrs, 'toolparamdescription=') === false) {
                        $attrs .= ' toolparamdescription="' . esc_attr($desc) . '"';
                    }
                    return '<' . $m[1] . $attrs . ($selfClose ? ' />' : '>');
                },
                $html
            ) ?? $html;
        }
        return $html;
    }
}
