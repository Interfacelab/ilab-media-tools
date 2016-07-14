#!/bin/bash

find vendor/aws/aws-sdk-php/src/ -type f -exec sed -i '' 's#use Aws\\#use ILAB_Aws\\#g' {} +
find vendor/aws/aws-sdk-php/src/ -type f -exec sed -i '' 's#namespace Aws#namespace ILAB_Aws#g' {} +
find vendor/aws/aws-sdk-php/src/ -type f -exec sed -i '' 's#"Aws\\#"ILAB_Aws\\#g' {} +
find vendor/aws/aws-sdk-php/src/ -type f -exec sed -i '' 's#'"'"'Aws\\#'"'"'ILAB_Aws\\#g' {} +
find vendor/aws/aws-sdk-php/src/ -type f -exec sed -i '' 's#\\Aws\\#\\ILAB_Aws\\#g' {} +
