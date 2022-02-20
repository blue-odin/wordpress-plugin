#!/bin/bash

set -e
set -u

packagename=blueodin-plugin

git archive HEAD --prefix=$packagename/ --format=zip -o ../$packagename.zip
