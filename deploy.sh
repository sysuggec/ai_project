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
DEFAULT_ENV="prod"  # 默认生产环境

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
    start       构建并启动服务（自动检测并构建基础镜像）
    stop        停止服务
    restart     重启服务
    rebuild     重新构建并启动
    build-base  手动构建基础镜像（包含系统依赖，加速后续构建）
    reset       重置数据并重新部署（清理数据库和上传文件）
    logs        查看日志
    status      查看服务状态
    clean       清理容器和镜像
    help        显示帮助信息

选项:
    -p, --port PORT           宿主机端口 (默认: $DEFAULT_PORT)
    -u, --upload PATH         上传文件存储路径 (默认: $DEFAULT_UPLOAD_PATH)
    -d, --db PATH             数据库存储路径 (默认: $DEFAULT_DB_PATH)
    -m, --mode MODE           部署模式: dev 或 prod (默认: $DEFAULT_ENV)
    -e, --env FILE            环境配置文件 (默认: .env)
    --no-cache                强制重新构建（不使用缓存）

示例:
    $0 start                          # 生产模式启动（默认）
    $0 start --mode dev               # 开发模式启动（挂载源代码，实时生效）
    $0 start --mode prod              # 生产模式启动（使用缓存镜像）
    $0 start -p 9000 --mode dev       # 开发模式，自定义端口
    $0 build-base                     # 构建基础镜像（首次部署或系统依赖更新时执行）
    $0 rebuild                        # 重新构建并启动
    $0 rebuild --mode dev             # 重新构建并启动开发模式
    $0 rebuild --no-cache             # 强制重新构建（不使用缓存）
    $0 reset                          # 重置所有数据并重新部署
    $0 stop                           # 停止服务
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

# 获取 docker-compose 文件
get_compose_file() {
    local mode=$1
    if [[ "$mode" == "dev" ]]; then
        echo "-f docker-compose.dev.yml"
    else
        echo "-f docker-compose.yml"
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
    local mode=$5

    info "开始部署资源上传下载系统（${mode} 模式）..."
    echo ""

    # 检查环境
    check_docker

    # 生产模式需要检查基础镜像
    if [[ "$mode" == "prod" ]]; then
        if ! docker image inspect resource-system-base:latest &>/dev/null; then
            warn "基础镜像不存在，正在自动构建..."
            echo ""
            build_base
            echo ""
        fi
    fi

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

    # 获取 compose 命令和文件
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")

    info "配置信息:"
    echo "  - 部署模式: $mode"
    echo "  - 端口映射: $port -> 80"
    echo "  - 上传目录: $upload_path"
    echo "  - 数据库目录: $db_path"
    if [[ "$mode" == "dev" ]]; then
        echo "  - 源代码: 已挂载（实时生效）"
    fi
    echo ""

    # 构建 and 启动
    info "构建 Docker 镜像..."
    $compose_cmd $compose_file build $NO_CACHE

    info "启动服务..."
    $compose_cmd $compose_file up -d

    echo ""
    success "部署完成!"
    echo ""
    echo "访问地址: http://localhost:$port"
    echo ""
    echo "常用命令:"
    echo "  查看日志: $compose_cmd $compose_file logs -f"
    echo "  停止服务: $compose_cmd $compose_file down"
    echo "  重启服务: $compose_cmd $compose_file restart"
    if [[ "$mode" == "dev" ]]; then
        echo ""
        echo "开发提示:"
        echo "  修改 src/backend/ 代码后自动生效，无需重启容器"
        echo "  前端代码修改后需要在 src/frontend/ 目录执行: npm run build"
    fi
}

# 停止服务
stop_service() {
    local mode=$1
    info "停止服务（${mode} 模式）..."
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")
    $compose_cmd $compose_file down
    success "服务已停止"
}

# 重启服务
restart_service() {
    local mode=$1
    info "重启服务（${mode} 模式）..."
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")
    $compose_cmd $compose_file restart
    success "服务已重启"
}

# 重新构建
rebuild_service() {
    local mode=$1
    info "重新构建服务（${mode} 模式）..."
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")
    $compose_cmd $compose_file down
    $compose_cmd $compose_file build $NO_CACHE
    $compose_cmd $compose_file up -d
    success "重新构建完成"
}

# 构建基础镜像
build_base() {
    info "构建基础镜像..."
    docker build -f docker/Dockerfile.base -t resource-system-base:latest .
    success "基础镜像构建完成: resource-system-base:latest"
    echo ""
    echo "提示: 基础镜像包含系统依赖，后续构建将显著加速"
    echo "当系统依赖（nginx, sqlite 等）需要更新时，重新执行此命令"
}

# 查看日志
view_logs() {
    local mode=$1
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")
    $compose_cmd $compose_file logs -f
}

# 查看状态
view_status() {
    local mode=$1
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")
    $compose_cmd $compose_file ps
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
    local port=$3
    local env_file=$4
    local mode=$5

    warn "⚠️  这将删除所有数据（数据库和上传文件），是否继续? [y/N]"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        info "已取消"
        exit 0
    fi

    info "停止服务..."
    local compose_cmd=$(get_compose_cmd)
    local compose_file=$(get_compose_file "$mode")
    $compose_cmd $compose_file down 2>/dev/null || true

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
    start_service "$port" "$upload_path" "$db_path" "$env_file" "$mode"
}

# 解析参数
parse_args() {
    COMMAND=""
    PORT=$DEFAULT_PORT
    UPLOAD_PATH=$DEFAULT_UPLOAD_PATH
    DB_PATH=$DEFAULT_DB_PATH
    ENV_FILE=".env"
    MODE=$DEFAULT_ENV
    NO_CACHE=""

    while [[ $# -gt 0 ]]; do
        case $1 in
            start|stop|restart|rebuild|build-base|reset|logs|status|clean|help)
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
            -m|--mode)
                MODE="$2"
                if [[ "$MODE" != "dev" && "$MODE" != "prod" ]]; then
                    error "无效的部署模式: $MODE (必须是 dev 或 prod)"
                fi
                shift 2
                ;;
            -e|--env)
                ENV_FILE="$2"
                shift 2
                ;;
            --no-cache)
                NO_CACHE="--no-cache"
                shift
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
            start_service "$PORT" "$UPLOAD_PATH" "$DB_PATH" "$ENV_FILE" "$MODE"
            ;;
        stop)
            stop_service "$MODE"
            ;;
        restart)
            restart_service "$MODE"
            ;;
        rebuild)
            rebuild_service "$MODE"
            ;;
        build-base)
            build_base
            ;;
        reset)
            reset_data "$UPLOAD_PATH" "$DB_PATH" "$PORT" "$ENV_FILE" "$MODE"
            ;;
        logs)
            view_logs "$MODE"
            ;;
        status)
            view_status "$MODE"
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
