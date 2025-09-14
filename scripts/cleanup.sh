#!/bin/bash

echo "Cleaning up PHP CRUD Application from Kubernetes..."

kubectl delete -f k8s/ --ignore-not-found=true

echo "Cleanup completed!"
echo ""
echo "To also remove persistent data:"
echo "  kubectl delete pvc mysql-pvc -n crud-app"
echo "  kubectl delete namespace crud-app"