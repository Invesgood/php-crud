#!/bin/bash
set -e

echo "Deploying PHP CRUD Application to Kubernetes..."

# Check kubectl connection
if ! kubectl cluster-info &> /dev/null; then
    echo "Error: Cannot connect to Kubernetes cluster"
    echo "Please check your kubeconfig and cluster connection"
    exit 1
fi

# Deploy in order
echo "1. Creating namespace..."
kubectl apply -f k8s/namespace.yaml

echo "2. Creating secrets and configmaps..."
kubectl apply -f k8s/mysql-secret.yaml
kubectl apply -f k8s/mysql-configmap.yaml
kubectl apply -f k8s/php-app-configmap.yaml

echo "3. Creating persistent volume claim..."
kubectl apply -f k8s/mysql-pvc.yaml

echo "4. Deploying MySQL..."
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml

echo "5. Waiting for MySQL to be ready..."
kubectl wait --for=condition=ready pod -l app=mysql -n crud-app --timeout=300s

echo "6. Deploying PHP application..."
kubectl apply -f k8s/php-app-deployment.yaml
kubectl apply -f k8s/php-app-service.yaml

echo "7. Creating ingress..."
kubectl apply -f k8s/ingress.yaml

echo "8. Setting up autoscaling..."
kubectl apply -f k8s/hpa.yaml

echo "Deployment completed!"
echo ""
echo "Check status:"
echo "  kubectl get all -n crud-app"
echo ""
echo "Access application:"
echo "  kubectl port-forward svc/php-app-service 8080:80 -n crud-app"
echo "  Then open http://localhost:8080"
echo ""
echo "Or configure your hosts file:"
echo "  echo '127.0.0.1 crud-app.local' >> /etc/hosts"
echo "  kubectl port-forward svc/nginx-controller 80:80 -n ingress-nginx"
echo "  Then open http://crud-app.local"