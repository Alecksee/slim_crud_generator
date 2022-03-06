<?php

function getUserInput($question){
    echo $question;
    $input = fgets(STDIN, 1024);
    return rtrim($input);
}

function isPlurial($str){
    return substr($str, -1) === "s";
}

function plurialize($str) {
    $str = strtolower($str);
    if (isPlurial($str))
        return $str;
    return $str.'s';
}