<?php

namespace App\Service;

class Slugify
{
    const UTF8 = [
        '/[áàâãªä]/u' => 'a',
        '/[ÁÀÂÃÄ]/u' => 'A',
        '/[ÍÌÎÏ]/u' => 'I',
        '/[íìîï]/u' => 'i',
        '/[éèêë]/u' => 'e',
        '/[ÉÈÊË]/u' => 'E',
        '/[óòôõºö]/u' => 'o',
        '/[ÓÒÔÕÖ]/u' => 'O',
        '/[úùûü]/u' => 'u',
        '/[ÚÙÛÜ]/u' => 'U',
        '/ç/' => 'c',
        '/Ç/' => 'C',
        '/ñ/' => 'n',
        '/Ñ/' => 'N',
    ];

    /**
     * @param string $input
     * @return string
     */
    public function generate(string $input) : string
    {
        $output = $this->killSpecialChar(trim($input));
        $output = $this->cleanSlug(trim($output));
        $output = strtolower($output);
        return $output;
    }

    /**
     * @param string $slug
     * @return string|string[]|null
     */
    private function cleanSlug(string $slug) : string
    {
        $slug = preg_replace('/[^A-Za-z0-9- ]/', '', $slug);
        $slug = str_replace(' ', '-', trim($slug));
        return preg_replace('/-+/', '-', $slug);
    }

    /**
     * @param $text
     * @return string|string[]|null
     */
    private function killSpecialChar($text) : string
    {
        return preg_replace(array_keys(self::UTF8), array_values(self::UTF8), $text);
    }
}
