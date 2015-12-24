<?php namespace BapCat\Remodel;

use ICanBoogie\Inflector;

function titleize($input) {
  return Inflector::get()->titleize($input);
}

function camelize($input, $lowercase_first = false) {
  return Inflector::get()->camelize($input, $lowercase_first ? Inflector::DOWNCASE_FIRST_LETTER : Inflector::UPCASE_FIRST_LETTER);
}

function underscore($input) {
  return Inflector::get()->underscore($input);
}

function pluralize($input) {
  return Inflector::get()->pluralize($input);
}

function singularize($input) {
  return Inflector::get()->singularize($input);
}

function humanize($input) {
  return Inflector::get()->humanize($input);
}
