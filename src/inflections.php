<?php declare(strict_types=1); namespace BapCat\Remodel;

use ICanBoogie\Inflector;

/**
 * @param  string  $input
 *
 * @return  string
 */
function titleize(string $input): string {
  return Inflector::get()->titleize($input);
}

/**
 * @param  string  $input
 * @param  bool    $lowercase_first
 *
 * @return  string
 */
function camelize(string $input, bool $lowercase_first = false): string {
  return Inflector::get()->camelize($input, $lowercase_first ? Inflector::DOWNCASE_FIRST_LETTER : Inflector::UPCASE_FIRST_LETTER);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function underscore(string $input): string {
  return Inflector::get()->underscore($input);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function pluralize(string $input): string {
  return Inflector::get()->pluralize($input);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function singularize(string $input): string {
  return Inflector::get()->singularize($input);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function humanize(string $input): string {
  return Inflector::get()->humanize($input);
}

/**
 * @param  string  $class_name
 *
 * @return  string
 */
function short(string $class_name): string {
  $parts = explode('\\', $class_name);
  return array_pop($parts);
}

/**
 * @param  string  $name
 *
 * @return  string
 */
function keyify(string $name): string {
  return underscore($name) . '_id';
}
