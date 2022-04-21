<?php

$defString = 'Я люблю море. Я лечу на море. Я умею плавать в море. Какое чистое море! Хочу на море. Завтра поедем на море.';

/*
 * Разбиваю исходную строку на массив слов (разделитель - пробел)
 * Предварительно очистив строку от знаков препинания
 */
$defArr = explode(' ', str_replace(['.', '!'], '', $defString));

/*
 * Подсчитываю частоту вхождения каждого слова в исходный массив
 * Создаю новый массив со словами в ключе и их частотой в значении
 */
$valArr = array_count_values($defArr);

/*
 * Возвращаю слово с наибольшей чистотой вхождения ("повторяющееся слово")
 */
$defWord = array_search(max($valArr), $valArr);

$revWord = strrev($defWord);
$newString = str_replace($defWord, $revWord, $defString);

var_dump($newString);
