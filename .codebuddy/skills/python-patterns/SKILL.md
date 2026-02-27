---
name: python-patterns
description: Python惯用法、PEP 8标准、类型提示和构建健壮、高效、可维护Python应用的最佳实践。
origin: ECC
allowed-tools: 
disable: true
---

# Python开发模式

构建健壮、高效、可维护应用的Python惯用模式和最佳实践。

## 何时激活

- 编写新Python代码
- 审查Python代码
- 重构现有Python代码
- 设计Python包/模块

## 核心原则

### 1. 可读性很重要

Python优先考虑可读性。代码应该显而易见且易于理解。

```python
# 好：清晰可读
def get_active_users(users: list[User]) -> list[User]:
    """从提供的列表中返回活跃用户。"""
    return [user for user in users if user.is_active]


# 坏：聪明但令人困惑
def get_active_users(u):
    return [x for x in u if x.a]
```

### 2. 显式优于隐式

避免魔法；清楚说明代码做什么。

```python
# 好：显式配置
import logging

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)

# 坏：隐藏的副作用
import some_module
some_module.setup()  # 这做什么？
```

### 3. EAFP - 请求原谅比许可更容易

Python偏好异常处理而非检查条件。

```python
# 好：EAFP风格
def get_value(dictionary: dict, key: str) -> Any:
    try:
        return dictionary[key]
    except KeyError:
        return default_value

# 坏：LBYL（三思而后行）风格
def get_value(dictionary: dict, key: str) -> Any:
    if key in dictionary:
        return dictionary[key]
    else:
        return default_value
```

## 类型提示

### 基本类型注解

```python
from typing import Optional, List, Dict, Any

def process_user(
    user_id: str,
    data: Dict[str, Any],
    active: bool = True
) -> Optional[User]:
    """处理用户并返回更新的User或None。"""
    if not active:
        return None
    return User(user_id, data)
```

### 现代类型提示（Python 3.9+）

```python
# Python 3.9+ - 使用内置类型
def process_items(items: list[str]) -> dict[str, int]:
    return {item: len(item) for item in items}

# Python 3.8及更早 - 使用typing模块
from typing import List, Dict

def process_items(items: List[str]) -> Dict[str, int]:
    return {item: len(item) for item in items}
```

### 类型别名和TypeVar

```python
from typing import TypeVar, Union

# 复杂类型的类型别名
JSON = Union[dict[str, Any], list[Any], str, int, float, bool, None]

def parse_json(data: str) -> JSON:
    return json.loads(data)

# 泛型类型
T = TypeVar('T')

def first(items: list[T]) -> T | None:
    """返回第一个元素，如果列表为空则返回None。"""
    return items[0] if items else None
```

### 基于Protocol的鸭子类型

```python
from typing import Protocol

class Renderable(Protocol):
    def render(self) -> str:
        """将对象渲染为字符串。"""

def render_all(items: list[Renderable]) -> str:
    """渲染所有实现Renderable协议的项目。"""
    return "\n".join(item.render() for item in items)
```

## 错误处理模式

### 特定异常处理

```python
# 好：捕获特定异常
def load_config(path: str) -> Config:
    try:
        with open(path) as f:
            return Config.from_json(f.read())
    except FileNotFoundError as e:
        raise ConfigError(f"配置文件未找到: {path}") from e
    except json.JSONDecodeError as e:
        raise ConfigError(f"配置中JSON无效: {path}") from e

# 坏：裸except
def load_config(path: str) -> Config:
    try:
        with open(path) as f:
            return Config.from_json(f.read())
    except:
        return None  # 静默失败！
```

### 异常链

```python
def process_data(data: str) -> Result:
    try:
        parsed = json.loads(data)
    except json.JSONDecodeError as e:
        # 链接异常以保留回溯
        raise ValueError(f"解析数据失败: {data}") from e
```

### 自定义异常层次结构

```python
class AppError(Exception):
    """所有应用错误的基异常。"""
    pass

class ValidationError(AppError):
    """输入验证失败时引发。"""
    pass

class NotFoundError(AppError):
    """请求的资源未找到时引发。"""
    pass

# 使用
def get_user(user_id: str) -> User:
    user = db.find_user(user_id)
    if not user:
        raise NotFoundError(f"用户未找到: {user_id}")
    return user
```

## 上下文管理器

### 资源管理

```python
# 好：使用上下文管理器
def process_file(path: str) -> str:
    with open(path, 'r') as f:
        return f.read()

# 坏：手动资源管理
def process_file(path: str) -> str:
    f = open(path, 'r')
    try:
        return f.read()
    finally:
        f.close()
```

### 自定义上下文管理器

```python
from contextlib import contextmanager

@contextmanager
def timer(name: str):
    """计时代码块的上下文管理器。"""
    start = time.perf_counter()
    yield
    elapsed = time.perf_counter() - start
    print(f"{name} 耗时 {elapsed:.4f} 秒")

# 使用
with timer("数据处理"):
    process_large_dataset()
```

### 上下文管理器类

```python
class DatabaseTransaction:
    def __init__(self, connection):
        self.connection = connection

    def __enter__(self):
        self.connection.begin_transaction()
        return self

    def __exit__(self, exc_type, exc_val, exc_tb):
        if exc_type is None:
            self.connection.commit()
        else:
            self.connection.rollback()
        return False  # 不抑制异常

# 使用
with DatabaseTransaction(conn):
    user = conn.create_user(user_data)
    conn.create_profile(user.id, profile_data)
```

## 推导式和生成器

### 列表推导式

```python
# 好：简单转换的列表推导式
names = [user.name for user in users if user.is_active]

# 坏：手动循环
names = []
for user in users:
    if user.is_active:
        names.append(user.name)

# 复杂推导式应该展开
# 坏：太复杂
result = [x * 2 for x in items if x > 0 if x % 2 == 0]

# 好：使用生成器函数
def filter_and_transform(items: Iterable[int]) -> list[int]:
    result = []
    for x in items:
        if x > 0 and x % 2 == 0:
            result.append(x * 2)
    return result
```

### 生成器表达式

```python
# 好：惰性求值的生成器
total = sum(x * x for x in range(1_000_000))

# 坏：创建大型中间列表
total = sum([x * x for x in range(1_000_000)])
```

### 生成器函数

```python
def read_large_file(path: str) -> Iterator[str]:
    """逐行读取大文件。"""
    with open(path) as f:
        for line in f:
            yield line.strip()

# 使用
for line in read_large_file("huge.txt"):
    process(line)
```

## 数据类和命名元组

### 数据类

```python
from dataclasses import dataclass, field
from datetime import datetime

@dataclass
class User:
    """具有自动__init__、__repr__和__eq__的用户实体。"""
    id: str
    name: str
    email: str
    created_at: datetime = field(default_factory=datetime.now)
    is_active: bool = True

# 使用
user = User(
    id="123",
    name="Alice",
    email="alice@example.com"
)
```

### 带验证的数据类

```python
@dataclass
class User:
    email: str
    age: int

    def __post_init__(self):
        # 验证邮箱格式
        if "@" not in self.email:
            raise ValueError(f"无效邮箱: {self.email}")
        # 验证年龄范围
        if self.age < 0 or self.age > 150:
            raise ValueError(f"无效年龄: {self.age}")
```

### 命名元组

```python
from typing import NamedTuple

class Point(NamedTuple):
    """不可变2D点。"""
    x: float
    y: float

    def distance(self, other: 'Point') -> float:
        return ((self.x - other.x) ** 2 + (self.y - other.y) ** 2) ** 0.5

# 使用
p1 = Point(0, 0)
p2 = Point(3, 4)
print(p1.distance(p2))  # 5.0
```

## 装饰器

### 函数装饰器

```python
import functools
import time

def timer(func: Callable) -> Callable:
    """计时函数执行的装饰器。"""
    @functools.wraps(func)
    def wrapper(*args, **kwargs):
        start = time.perf_counter()
        result = func(*args, **kwargs)
        elapsed = time.perf_counter() - start
        print(f"{func.__name__} 耗时 {elapsed:.4f}s")
        return result
    return wrapper

@timer
def slow_function():
    time.sleep(1)

# slow_function() 打印: slow_function 耗时 1.0012s
```

### 参数化装饰器

```python
def repeat(times: int):
    """多次重复函数的装饰器。"""
    def decorator(func: Callable) -> Callable:
        @functools.wraps(func)
        def wrapper(*args, **kwargs):
            results = []
            for _ in range(times):
                results.append(func(*args, **kwargs))
            return results
        return wrapper
    return decorator

@repeat(times=3)
def greet(name: str) -> str:
    return f"你好, {name}!"

# greet("Alice") 返回 ["你好, Alice!", "你好, Alice!", "你好, Alice!"]
```

### 基于类的装饰器

```python
class CountCalls:
    """计数函数调用次数的装饰器。"""
    def __init__(self, func: Callable):
        functools.update_wrapper(self, func)
        self.func = func
        self.count = 0

    def __call__(self, *args, **kwargs):
        self.count += 1
        print(f"{self.func.__name__} 已被调用 {self.count} 次")
        return self.func(*args, **kwargs)

@CountCalls
def process():
    pass

# 每次调用 process() 打印调用次数
```

## 并发模式

### I/O密集型任务的线程

```python
import concurrent.futures
import threading

def fetch_url(url: str) -> str:
    """获取URL（I/O密集型操作）。"""
    import urllib.request
    with urllib.request.urlopen(url) as response:
        return response.read().decode()

def fetch_all_urls(urls: list[str]) -> dict[str, str]:
    """使用线程并发获取多个URL。"""
    with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
        future_to_url = {executor.submit(fetch_url, url): url for url in urls}
        results = {}
        for future in concurrent.futures.as_completed(future_to_url):
            url = future_to_url[future]
            try:
                results[url] = future.result()
            except Exception as e:
                results[url] = f"错误: {e}"
    return results
```

### CPU密集型任务的多进程

```python
def process_data(data: list[int]) -> int:
    """CPU密集型计算。"""
    return sum(x ** 2 for x in data)

def process_all(datasets: list[list[int]]) -> list[int]:
    """使用多进程处理多个数据集。"""
    with concurrent.futures.ProcessPoolExecutor() as executor:
        results = list(executor.map(process_data, datasets))
    return results
```

### 异步I/O的Async/Await

```python
import asyncio

async def fetch_async(url: str) -> str:
    """异步获取URL。"""
    import aiohttp
    async with aiohttp.ClientSession() as session:
        async with session.get(url) as response:
            return await response.text()

async def fetch_all(urls: list[str]) -> dict[str, str]:
    """并发获取多个URL。"""
    tasks = [fetch_async(url) for url in urls]
    results = await asyncio.gather(*tasks, return_exceptions=True)
    return dict(zip(urls, results))
```

## 包组织

### 标准项目布局

```
myproject/
├── src/
│   └── mypackage/
│       ├── __init__.py
│       ├── main.py
│       ├── api/
│       │   ├── __init__.py
│       │   └── routes.py
│       ├── models/
│       │   ├── __init__.py
│       │   └── user.py
│       └── utils/
│           ├── __init__.py
│           └── helpers.py
├── tests/
│   ├── __init__.py
│   ├── conftest.py
│   ├── test_api.py
│   └── test_models.py
├── pyproject.toml
├── README.md
└── .gitignore
```

### 导入约定

```python
# 好：导入顺序 - 标准库、第三方、本地
import os
import sys
from pathlib import Path

import requests
from fastapi import FastAPI

from mypackage.models import User
from mypackage.utils import format_name

# 好：使用isort自动排序导入
# pip install isort
```

### __init__.py用于包导出

```python
# mypackage/__init__.py
"""mypackage - 示例Python包。"""

__version__ = "1.0.0"

# 在包级别导出主要类/函数
from mypackage.models import User, Post
from mypackage.utils import format_name

__all__ = ["User", "Post", "format_name"]
```

## 内存和性能

### 使用__slots__提高内存效率

```python
# 坏：常规类使用__dict__（更多内存）
class Point:
    def __init__(self, x: float, y: float):
        self.x = x
        self.y = y

# 好：__slots__减少内存使用
class Point:
    __slots__ = ['x', 'y']

    def __init__(self, x: float, y: float):
        self.x = x
        self.y = y
```

### 大数据的生成器

```python
# 坏：在内存中返回完整列表
def read_lines(path: str) -> list[str]:
    with open(path) as f:
        return [line.strip() for line in f]

# 好：一次生成一行
def read_lines(path: str) -> Iterator[str]:
    with open(path) as f:
        for line in f:
            yield line.strip()
```

### 避免循环中的字符串拼接

```python
# 坏：由于字符串不可变性导致O(n²)
result = ""
for item in items:
    result += str(item)

# 好：使用join实现O(n)
result = "".join(str(item) for item in items)

# 好：使用StringIO构建
from io import StringIO

buffer = StringIO()
for item in items:
    buffer.write(str(item))
result = buffer.getvalue()
```

## Python工具集成

### 基本命令

```bash
# 代码格式化
black .
isort .

# Linting
ruff check .
pylint mypackage/

# 类型检查
mypy .

# 测试
pytest --cov=mypackage --cov-report=html

# 安全扫描
bandit -r .

# 依赖管理
pip-audit
safety check
```

### pyproject.toml配置

```toml
[project]
name = "mypackage"
version = "1.0.0"
requires-python = ">=3.9"
dependencies = [
    "requests>=2.31.0",
    "pydantic>=2.0.0",
]

[project.optional-dependencies]
dev = [
    "pytest>=7.4.0",
    "pytest-cov>=4.1.0",
    "black>=23.0.0",
    "ruff>=0.1.0",
    "mypy>=1.5.0",
]

[tool.black]
line-length = 88
target-version = ['py39']

[tool.ruff]
line-length = 88
select = ["E", "F", "I", "N", "W"]

[tool.mypy]
python_version = "3.9"
warn_return_any = true
warn_unused_configs = true
disallow_untyped_defs = true

[tool.pytest.ini_options]
testpaths = ["tests"]
addopts = "--cov=mypackage --cov-report=term-missing"
```

## 快速参考：Python惯用法

| 惯用法 | 描述 |
|--------|------|
| EAFP | 请求原谅比许可更容易 |
| 上下文管理器 | 使用 `with` 进行资源管理 |
| 列表推导式 | 用于简单转换 |
| 生成器 | 用于惰性求值和大数据集 |
| 类型提示 | 注解函数签名 |
| 数据类 | 用于自动生成方法的数据容器 |
| `__slots__` | 用于内存优化 |
| f-strings | 用于字符串格式化（Python 3.6+） |
| `pathlib.Path` | 用于路径操作（Python 3.4+） |
| `enumerate` | 用于循环中的索引-元素对 |

## 避免的反模式

```python
# 坏：可变默认参数
def append_to(item, items=[]):
    items.append(item)
    return items

# 好：使用None并创建新列表
def append_to(item, items=None):
    if items is None:
        items = []
    items.append(item)
    return items

# 坏：用type()检查类型
if type(obj) == list:
    process(obj)

# 好：使用isinstance
if isinstance(obj, list):
    process(obj)

# 坏：用 == 比较None
if value == None:
    process()

# 好：使用is
if value is None:
    process()

# 坏：from module import *
from os.path import *

# 好：显式导入
from os.path import join, exists

# 坏：裸except
try:
    risky_operation()
except:
    pass

# 好：特定异常
try:
    risky_operation()
except SpecificError as e:
    logger.error(f"操作失败: {e}")
```

**记住**：Python代码应该是可读的、显式的，并遵循最小惊讶原则。有疑问时，优先考虑清晰而非聪明。
