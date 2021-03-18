---
layout: default
title: Overview
---

<img class="banner" src="/images/commonmark-banner.png" alt="CommonMark for PHP" />

# Overview

[![Author](https://img.shields.io/badge/author-@markehalliwell-blue.svg?style=flat-square)](https://twitter.com/markehalliwell)
[![Latest Version](https://img.shields.io/packagist/v/unicorn-fail/emoji.svg?style=flat-square)](https://packagist.org/packages/unicorn-fail/emoji)
[![Total Downloads](https://img.shields.io/packagist/dt/unicorn-fail/emoji.svg?style=flat-square)](https://packagist.org/packages/unicorn-fail/emoji)
[![Software License](https://img.shields.io/badge/License-BSD--3-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/unicorn-fail/emoji/Tests/latest.svg?style=flat-square)](https://github.com/unicorn-fail/emoji/actions?query=workflow%3ATests+branch%3Alatest)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/unicorn-fail/emoji.svg?style=flat-square)](https://scrutinizer-ci.com/g/unicorn-fail/emoji/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/unicorn-fail/emoji.svg?style=flat-square)](https://scrutinizer-ci.com/g/unicorn-fail/emoji)

{{ site.data.project.highlights.description }}

## Installation

This library can be installed via Composer:

~~~bash
composer require league/emoji
~~~

See the [installation](/2.0/installation/) section for more details.

## Basic Usage

Simply instantiate the converter and start converting some Markdown to HTML!

~~~php
<?php

use League\CommonMark\CommonMarkConverter;

$converter = new CommonMarkConverter();
echo $converter->convertToHtml('# Hello World!');

// <h1>Hello World!</h1>
~~~

<i class="fa fa-exclamation-triangle"></i>
**Important:** See the [basic usage](/2.0/basic-usage/) and [security](/2.0/security/) sections for important details.
