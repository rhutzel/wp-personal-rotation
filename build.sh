#!/bin/bash

# Builds and packages the plugin for distribution.

filename="Personal-Rotation-1.0.0.zip"

echo "# Clean"
rm $filename

echo "# Archive"
zip -vr $filename personal-rotation.php languages/*.mo

