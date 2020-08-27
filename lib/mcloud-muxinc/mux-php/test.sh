#!/bin/bash
set -euo pipefail

if [ -z "${MUX_TOKEN_ID:-}" ]
then
      echo "MUX_TOKEN_ID not set"
      exit 255
fi

if [ -z "${MUX_TOKEN_SECRET:-}" ]
then
      echo "MUX_TOKEN_SECRET not set"
      exit 255
fi

VIDEO_TESTS=./examples/video/exercise*.php
for f in $VIDEO_TESTS
do
  echo "========== Running $f =========="
    php $f
done

DATA_TESTS=./examples/data/exercise*.php
for f in $DATA_TESTS
do
  echo "========== Running $f =========="
    php $f
done
