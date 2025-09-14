#!/bin/bash
set -e

echo "Building PHP CRUD Application..."

# Build Docker image
docker build -t php-crud-app:latest .

# Tag dengan timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker tag php-crud-app:latest php-crud-app:$TIMESTAMP

echo "Build completed!"
echo "Images:"
echo "  php-crud-app:latest"
echo "  php-crud-app:$TIMESTAMP"

# Optional: Push ke registry
read -p "Push to registry? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    REGISTRY=${1:-"your-registry"}
    docker tag php-crud-app:latest $REGISTRY/php-crud-app:latest
    docker tag php-crud-app:latest $REGISTRY/php-crud-app:$TIMESTAMP
    
    docker push $REGISTRY/php-crud-app:latest
    docker push $REGISTRY/php-crud-app:$TIMESTAMP
    echo "Images pushed to $REGISTRY"
fi