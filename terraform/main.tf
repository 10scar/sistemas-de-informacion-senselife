# SenseLife — EC2 capa gratuita fija (t3.micro, us-east-1).
# Variables solo para app/repo (terraform.tfvars).
#
#   cd terraform
#   terraform init
#   terraform apply

terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

variable "git_repo_url" {
  type        = string
  description = "URL HTTPS del repositorio a clonar en la EC2"
}

variable "git_branch" {
  type        = string
  description = "Rama del repositorio"
  default     = "main"
}

variable "db_password" {
  type        = string
  sensitive   = true
  description = "Contraseña de Postgres"
}

variable "internal_api_token" {
  type        = string
  sensitive   = true
  description = "Token compartido entre senselife-system y senselife-data"
}

provider "aws" {
  region = "us-east-1"
}

data "aws_vpc" "default" {
  default = true
}

# us-east-1e no admite t3.micro; usamos subnet en AZ soportada.
data "aws_subnet" "senselife" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.default.id]
  }

  filter {
    name   = "availability-zone"
    values = ["us-east-1a"]
  }

  filter {
    name   = "default-for-az"
    values = ["true"]
  }
}

data "aws_ami" "amazon_linux" {
  most_recent = true
  owners      = ["amazon"]

  filter {
    name   = "name"
    values = ["al2023-ami-*-x86_64"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }
}

resource "aws_security_group" "senselife" {
  name        = "senselife-ec2-free"
  description = "HTTP + telemetria + SSH"
  vpc_id      = data.aws_vpc.default.id

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 3001
    to_port     = 3001
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_instance" "senselife" {
  ami                         = data.aws_ami.amazon_linux.id
  instance_type               = "t3.micro"
  subnet_id                   = data.aws_subnet.senselife.id
  availability_zone           = data.aws_subnet.senselife.availability_zone
  vpc_security_group_ids      = [aws_security_group.senselife.id]
  associate_public_ip_address = true

  user_data_replace_on_change = true

  user_data = base64encode(<<-USERDATA
#!/bin/bash
set -euo pipefail
exec > /var/log/senselife-bootstrap.log 2>&1

echo "=== SenseLife bootstrap $(date -Is) ==="

dnf update -y
dnf install -y docker git openssl
systemctl enable --now docker

mkdir -p /usr/local/lib/docker/cli-plugins
curl -fsSL "https://github.com/docker/compose/releases/download/v2.24.6/docker-compose-linux-x86_64" \
  -o /usr/local/lib/docker/cli-plugins/docker-compose
chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

APP_DIR="/opt/senselife"
rm -rf "$APP_DIR"
mkdir -p "$APP_DIR"
git clone --depth 1 -b ${var.git_branch} ${var.git_repo_url} "$APP_DIR"
cd "$APP_DIR"

APP_KEY="base64:$(openssl rand -base64 32)"
DB_PASSWORD=${jsonencode(var.db_password)}
INTERNAL_TOKEN=${jsonencode(var.internal_api_token)}

cat > senselife-system/.env <<ENV
APP_NAME=SenseLife
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_URL=http://PLACEHOLDER
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
LOG_CHANNEL=stderr
LOG_LEVEL=info
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=senselife
DB_USERNAME=senselife
DB_PASSWORD=$DB_PASSWORD
DB_SSLMODE=prefer
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
TELEMETRIA_DATA_URL=http://api:3001
INTERNAL_API_TOKEN=$INTERNAL_TOKEN
TELEMETRIA_EXPORT_DISPOSITIVOS_PATH=/var/www/senselife-data-data/dispositivos.json
ENV

cat > senselife-data/.env <<ENV
MONGODB_URI=mongodb://mongo:27017
MONGODB_DATABASE=senselife_data
INTERNAL_TOKEN=$INTERNAL_TOKEN
SENSELIFE_SYSTEM_URL=http://web
DISPOSITIVOS_FILE=data/dispositivos.json
ENV

cat > docker-compose.ec2.yml <<'COMPOSE'
services:
  pgsql:
    image: postgres:17-alpine
    restart: unless-stopped
    environment:
      POSTGRES_DB: senselife
      POSTGRES_USER: senselife
      POSTGRES_PASSWORD: $${DB_PASSWORD}
    volumes:
      - pgsql-data:/var/lib/postgresql/data
    networks: [senselife]
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U senselife -d senselife"]
      interval: 15s
      timeout: 5s
      retries: 15

  redis:
    image: redis:7-alpine
    restart: unless-stopped
    networks: [senselife]

  mongo:
    image: mongo:7
    restart: unless-stopped
    volumes: [mongo-data:/data/db]
    networks: [senselife]
    healthcheck:
      test: ["CMD", "mongosh", "--eval", "db.adminCommand('ping')"]
      interval: 15s
      timeout: 5s
      retries: 15

  api:
    build: { context: ./senselife-data }
    restart: unless-stopped
    env_file: [./senselife-data/.env]
    environment:
      MONGODB_URI: mongodb://mongo:27017
      PYTHONPATH: /app
    volumes:
      - ./senselife-data/data:/app/data
    ports: ["3001:3001"]
    depends_on:
      mongo: { condition: service_healthy }
    networks: [senselife]

  app:
    build: { context: ./senselife-system }
    restart: unless-stopped
    env_file: [./senselife-system/.env]
    environment:
      DB_HOST: pgsql
      REDIS_HOST: redis
      TELEMETRIA_DATA_URL: http://api:3001
    volumes:
      - storage:/var/www/html/storage
      - public_assets:/var/www/html/public
      - ./senselife-data/data:/var/www/senselife-data-data
    depends_on:
      pgsql: { condition: service_healthy }
      redis: { condition: service_started }
    networks: [senselife]

  web:
    image: nginx:1.27-alpine
    restart: unless-stopped
    ports: ["80:80"]
    volumes:
      - ./senselife-system/docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - public_assets:/var/www/html/public:ro
    depends_on: [app]
    networks: [senselife]

networks:
  senselife: { driver: bridge }

volumes:
  pgsql-data:
  mongo-data:
  storage:
  public_assets:
COMPOSE

export DB_PASSWORD="$DB_PASSWORD"
docker compose -f docker-compose.ec2.yml up -d --build

echo "Esperando servicios..."
sleep 60

PUBLIC_IP=$(curl -fsS http://169.254.169.254/latest/meta-data/public-ipv4 || echo "127.0.0.1")
sed -i "s|APP_URL=http://PLACEHOLDER|APP_URL=http://$PUBLIC_IP|" senselife-system/.env

docker compose -f docker-compose.ec2.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.ec2.yml exec -T app php artisan db:seed --force
docker compose -f docker-compose.ec2.yml exec -T app php artisan telemetria:export-dispositivos
docker compose -f docker-compose.ec2.yml exec -T app php artisan config:cache
docker compose -f docker-compose.ec2.yml up -d api
docker compose -f docker-compose.ec2.yml up -d

chown -R ec2-user:ec2-user "$APP_DIR" || true

echo "=== Listo: http://$PUBLIC_IP ==="
echo "Admin: http://$PUBLIC_IP/admin/login (test@example.com / password) ==="
USERDATA
  )

  root_block_device {
    volume_size = 30
    volume_type = "gp3"
  }

  tags = {
    Name = "senselife-free-tier"
  }
}

output "app_url" {
  description = "URL del portal (espera 5-10 min al bootstrap)"
  value       = "http://${aws_instance.senselife.public_ip}"
}

output "telemetria_api_url" {
  value = "http://${aws_instance.senselife.public_ip}:3001"
}

output "public_ip" {
  value = aws_instance.senselife.public_ip
}
