<?php namespace BapCat\Remodel;

use ICanBoogie\Inflector;

/**
 * @param  string  $input
 *
 * @return  string
 */
function titleize($input) {
  return Inflector::get()->titleize($input);
}

/**
 * @param  string  $input
 * @param  bool    $lowercase_first
 *
 * @return  string
 */
function camelize($input, $lowercase_first = false) {
  return Inflector::get()->camelize($input, $lowercase_first ? Inflector::DOWNCASE_FIRST_LETTER : Inflector::UPCASE_FIRST_LETTER);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function underscore($input) {
  return Inflector::get()->underscore($input);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function pluralize($input) {
  return Inflector::get()->pluralize($input);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function singularize($input) {
  return Inflector::get()->singularize($input);
}

/**
 * @param  string  $input
 *
 * @return  string
 */
function humanize($input) {
  return Inflector::get()->humanize($input);
}

/**
 * @param  string  $class_name
 *
 * @return  string
 */
function short($class_name) {
  $parts = explode('\\', $class_name);
  return array_pop($parts);
}

/**
 * @param  string  $name
 *
 * @return  string
 */
function keyify($name) {
  return underscore($name) . '_id';
}
