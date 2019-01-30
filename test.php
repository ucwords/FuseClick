<?php
/**
 * Created by PhpStorm.
 * User: suwan
 * Date: 2018/6/22
 * Time: 11:52
 */
$url="https://lh3.googleusercontent.com/ARj0yd5DGocKJjiIiJOONr6aI1p5Br0H8TidhQpOxNLFXJsOZO7mWuFghsJwrYacAkAN=w300";
$im = new imagick($url);
$im->setImageFormat("png");
$im->writeImage('test.png');
$im->clear();
$im->destroy();