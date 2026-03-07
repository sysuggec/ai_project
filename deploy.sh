#!/bin/bash

# ============================================
# 资源上传下载系统 - Docker 一键部署脚本
# ============================================

set -e

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 默认配置
DEFAULT_PORT=8080
DEFAULT_UPLOAD_PATH="./data/upload"
DEFAULT_DB_PATH="./data/db"

# 打印带颜色的信息
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# 显示帮助
show_help() {
    cat << EOF
资源上传下载系统 - Docker 一键部署脚本

用法: $0 [命令] [选项]

命令:
    start       构建并启动服务
    stop        停止服务
    restart     重启服务
    rebuild     重新构建并启动
    reset       重置数据并重新部署（清理数据库和上传文件）
    logs        查看日志
    status      查看服务状态
    clean       清理容器和镜像
    help        显示帮助信息

选项:
    -p, --port PORT           宿主机端口 (默认: $DEFAULT_PORT)
    -u, --upload PATH         上传文件存储路径 (默认: $DEFAULT_UPLOAD_PATH)
    -d, --db PATH             数据库存储路径 (默认: $DEFAULT_DB_PATH)
    -e, --env FILE            环境配置文件 (默认: .env)

示例:
    $0 start                          # 使用默认配置启动
    $0 start -p 9000                  # 使用端口 9000 启动
    $0 start -p 9000 -u /data/upload  # 自定义端口和上传目录
    $0 reset                          # 重置所有数据并重新部署
    $0 stop                           # 停止服务
    $0 rebuild                        # 重新构建并启动
    $0 logs                           # 查看日志

EOF
}

# 检查 Docker 是否安装
check_docker() {
    if ! command -v docker &> /dev/null; then
        error "Docker 未安装，请先安装 Docker"
    fi

    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        error "Docker Compose 未安装，请先安装 Docker Compose"
    fi

    # 检查 Docker 是否运行
    if ! docker info &> /dev/null; then
        error "Docker 未运行，请先启动 Docker"
    fi
}

# 获取 docker-compose 命令
get_compose_cmd() {
    if docker compose version &> /dev/null; then
        echo "docker compose"
    else
        echo "docker-compose"
    fi
}

# 创建必要的目录
create_directories() {
    local upload_path=$1
    local db_path=$2

    info "创建存储目录..."

    # 创建上传目录
    if [[ "$upload_path" != /* ]]; then
        upload_path="$(pwd)/$upload_path"
    fi
    mkdir -p "$upload_path"
    success "上传目录: $upload_path"

    # 创建数据库目录
    if [[ "$db_path" != /* ]]; then
        db_path="$(pwd)/$db_path"
    fi
    mkdir -p "$db_path"
    success "数据库目录: $db_path"
}

# 启动服务
start_service() {
    local port=$1
    local upload_path=$2
    local db_path=$3
    local env_file=$4

    info "开始部署资源上传下载系统..."
    echo ""

    # 检查环境
    check_docker

    # 创建目录
    create_directories "$upload_path" "$db_path"

    # 创建/更新 .env 文件
    cat > .env << EOF
HOST_PORT=$port
UPLOAD_PATH=$upload_path
DB_PATH=$db_path
PHP_UPLOAD_MAX_FILESIZE=0
PHP_POST_MAX_SIZE=0
PHP_MAX_EXECUTION_TIME=0
EOF

    info "配置信息:"
    echo "  - 端口映射: $port -> 80"
    echo "  - 上传目录: $upload_path"
    echo "  - 数据库目录: $db_path"
    echo ""

    # 构建 and 启动
    local compose_cmd=$(get_compose_cmd)

    info "构建 Docker 镜像..."
    $compose_cmd build --no-cache

    info "启动服务..."
    $compose_cmd up -d

    echo ""
    success "部署完成!"
    echo ""
    echo "访问地址: http://localhost:$port"
    echo ""
    echo "常用命令:"
    echo "  查看日志: $compose_cmd logs -f"
    echo "  停止服务: $compose_cmd down"
    echo "  重启服务: $compose_cmd restart"
}

# 停止服务
stop_service() {
    info "停止服务..."
    local compose_cmd=$(get_compose_cmd)
    $compose_cmd down
    success "服务已停止"
}

# 重启服务
restart_service() {
    info "重启服务..."
    local compose_cmd=$(get_compose_cmd)
    $compose_cmd restart
    success "服务已重启"
}

# 重新构建
rebuild_service() {
    info "重新构建服务..."
    local compose_cmd=$(get_compose_cmd)
    $compose_cmd down
    $compose_cmd build --no-cache
    $compose_cmd up -d
    success "重新构建完成"
}

# 查看日志
view_logs() {
    local compose_cmd=$(get_compose_cmd)
    $compose_cmd logs -f
}

# 查看状态
view_status() {
    local compose_cmd=$(get_compose_cmd)
    $compose_cmd ps
}

# 清理
clean() {
    warn "这将删除容器和镜像，是否继续? [y/N]"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        info "清理容器和镜像..."
        local compose_cmd=$(get_compose_cmd)
        $compose_cmd down --rmi all -v
        success "清理完成"
    else
        info "已取消"
    fi
}

# 重置数据
reset_data() {
    local upload_path=$1
    local db_path=$2

    warn "⚠️  这将删除所有数据（数据库和上传文件），是否继续? [y/N]"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        info "已取消"
        exit 0
    fi

    info "停止服务..."
    local compose_cmd=$(get_compose_cmd)
    $compose_cmd down 2>/dev/null || true

    info "清理数据目录..."

    # 清理上传目录
    if [[ "$upload_path" != /* ]]; then
        upload_path="$(pwd)/$upload_path"
    fi
    if [[ -d "$upload_path" ]]; then
        rm -rf "$upload_path"/*
        success "已清理上传目录: $upload_path"
    fi

    # 清理数据库目录
    if [[ "$db_path" != /* ]]; then
        db_path="$(pwd)/$db_path"
    fi
    if [[ -d "$db_path" ]]; then
        rm -rf "$db_path"/*
        success "已清理数据库目录: $db_path"
    fi

    info "重新部署..."
    start_service "$3" "$1" "$2" "$4"
}

# 解析参数
parse_args() {
    COMMAND=""
    PORT=$DEFAULT_PORT
    UPLOAD_PATH=$DEFAULT_UPLOAD_PATH
    DB_PATH=$DEFAULT_DB_PATH
    ENV_FILE=".env"

    while [[ $# -gt 0 ]]; do
        case $1 in
            start|stop|restart|rebuild|reset|logs|status|clean|help)
                COMMAND=$1
                shift
                ;;
            -p|--port)
                PORT="$2"
                shift 2
                ;;
            -u|--upload)
                UPLOAD_PATH="$2"
                shift 2
                ;;
            -d|--db)
                DB_PATH="$2"
                shift 2
                ;;
            -e|--env)
                ENV_FILE="$2"
                shift 2
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            *)
                error "未知参数: $1"
                ;;
        esac
    done

    # 如果没有命令，显示帮助
    if [[ -z "$COMMAND" ]]; then
        show_help
        exit 0
    fi
}

# 主函数
main() {
    parse_args "$@"

    case $COMMAND in
        start)
            start_service "$PORT" "$UPLOAD_PATH" "$DB_PATH" "$ENV_FILE"
            ;;
        stop)
            stop_service
            ;;
        restart)
            restart_service
            ;;
        rebuild)
            rebuild_service
            ;;
        reset)
            reset_data "$UPLOAD_PATH" "$DB_PATH" "$PORT" "$ENV_FILE"
            ;;
        logs)
            view_logs
            ;;
        status)
            view_status
            ;;
        clean)
            clean
            ;;
        help)
            show_help
            ;;
    esac
}

main "$@"
