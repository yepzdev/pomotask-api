<?php

// Configure headers to allow CORS (Cross-Origin Resource Sharing)
function setHeaders() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

/**
 * This function takes a text string and removes all characters
 * that are not letters (upper or lower case), digits, or space
 */
function filter_string_polyfill(string $string): string {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $string);
}

// This function validates if it is an integer
function filter_integer($value) {
    return filter_var($value, FILTER_VALIDATE_INT);
}