<?php
declare(strict_types=1);

/**
 * 四则混合运算计算器
 * 
 * 支持加减乘除和括号的混合运算
 */
class Calculator
{
    /** @var int 当前解析位置 */
    private int $position = 0;
    
    /** @var string 当前表达式 */
    private string $expression = '';

    /**
     * 计算表达式
     *
     * @param string $expression 数学表达式
     * @return float|int 计算结果
     * @throws InvalidArgumentException 表达式无效时抛出
     */
    public function calculate(string $expression)
    {
        // 去除空格
        $this->expression = str_replace(' ', '', $expression);
        $this->position = 0;
        
        if (empty($this->expression)) {
            throw new InvalidArgumentException('表达式不能为空');
        }
        
        // 验证表达式是否只包含合法字符
        if (!$this->isValidExpression($this->expression)) {
            throw new InvalidArgumentException('表达式包含非法字符');
        }
        
        $result = $this->parseExpression();
        
        // 如果结果是整数，返回整数类型
        if (is_float($result) && strpos((string)$result, '.') === false) {
            return (int)$result;
        }
        
        return $result;
    }

    /**
     * 验证表达式是否合法
     *
     * @param string $expression 表达式
     * @return bool 是否合法
     */
    private function isValidExpression(string $expression): bool
    {
        return preg_match('/^[0-9+\-*\/().]+$/', $expression) === 1;
    }

    /**
     * 解析表达式（递归下降解析器）
     *
     * @return float|int 计算结果
     */
    private function parseExpression()
    {
        return $this->parseAdditionSubtraction();
    }

    /**
     * 解析加减运算（最低优先级）
     *
     * @return float|int 计算结果
     */
    private function parseAdditionSubtraction()
    {
        $left = $this->parseMultiplicationDivision();
        
        while ($this->position < strlen($this->expression)) {
            $operator = $this->expression[$this->position];
            
            if ($operator !== '+' && $operator !== '-') {
                break;
            }
            
            $this->position++;
            $right = $this->parseMultiplicationDivision();
            
            if ($operator === '+') {
                $left = $left + $right;
            } else {
                $left = $left - $right;
            }
        }
        
        return $left;
    }

    /**
     * 解析乘除运算（中等优先级）
     *
     * @return float|int 计算结果
     */
    private function parseMultiplicationDivision()
    {
        $left = $this->parseNumberOrParentheses();
        
        while ($this->position < strlen($this->expression)) {
            $operator = $this->expression[$this->position];
            
            if ($operator !== '*' && $operator !== '/') {
                break;
            }
            
            $this->position++;
            $right = $this->parseNumberOrParentheses();
            
            if ($operator === '*') {
                $left = $left * $right;
            } else {
                if ($right == 0) {
                    throw new InvalidArgumentException('除数不能为零');
                }
                $left = $left / $right;
            }
        }
        
        return $left;
    }

    /**
     * 解析数字或括号表达式（最高优先级）
     *
     * @return float|int 计算结果
     */
    private function parseNumberOrParentheses()
    {
        // 处理负号
        if ($this->position < strlen($this->expression) 
            && $this->expression[$this->position] === '-') {
            $this->position++;
            return -$this->parseNumberOrParentheses();
        }
        
        // 处理正号
        if ($this->position < strlen($this->expression) 
            && $this->expression[$this->position] === '+') {
            $this->position++;
            return $this->parseNumberOrParentheses();
        }
        
        // 处理括号
        if ($this->position < strlen($this->expression) 
            && $this->expression[$this->position] === '(') {
            $this->position++; // 跳过 '('
            $result = $this->parseExpression();
            
            if ($this->position >= strlen($this->expression) 
                || $this->expression[$this->position] !== ')') {
                throw new InvalidArgumentException('括号不匹配');
            }
            
            $this->position++; // 跳过 ')'
            return $result;
        }
        
        // 解析数字
        return $this->parseNumber();
    }

    /**
     * 解析数字
     *
     * @return float 数字值
     */
    private function parseNumber(): float
    {
        $start = $this->position;
        
        while ($this->position < strlen($this->expression) 
            && (is_numeric($this->expression[$this->position]) 
                || $this->expression[$this->position] === '.')) {
            $this->position++;
        }
        
        if ($start === $this->position) {
            throw new InvalidArgumentException('无效的表达式');
        }
        
        return (float)substr($this->expression, $start, $this->position - $start);
    }
}

// 命令行使用示例
if (php_sapi_name() === 'cli') {
    $calculator = new Calculator();
    
    // 测试用例
    $testCases = [
        '1 + 2' => 3,
        '3 * 4' => 12,
        '10 / 2' => 5,
        '2 + 3 * 4' => 14,
        '(2 + 3) * 4' => 20,
        '10 - 2 * 3' => 4,
        '(10 - 2) * 3' => 24,
        '2 * 3 + 4 * 5' => 26,
        '((2 + 3) * (4 - 1))' => 15,
        '10 / 2 + 3 * 4 - 5' => 12,
    ];
    
    echo "=== 四则混合运算测试 ===\n\n";
    
    foreach ($testCases as $expression => $expected) {
        try {
            $result = $calculator->calculate($expression);
            $status = $result == $expected ? '✓' : '✗';
            echo sprintf(
                "%s %-25s = %-10s (期望: %s)\n",
                $status,
                $expression,
                $result,
                $expected
            );
        } catch (Exception $e) {
            echo sprintf("✗ %-25s 错误: %s\n", $expression, $e->getMessage());
        }
    }
    
    // 交互模式
    echo "\n=== 交互模式 ===\n";
    echo "输入表达式进行计算 (输入 'quit' 退出):\n";
    
    while (true) {
        echo "> ";
        $input = trim(fgets(STDIN));
        
        if ($input === 'quit' || $input === 'exit') {
            break;
        }
        
        if (empty($input)) {
            continue;
        }
        
        try {
            $result = $calculator->calculate($input);
            echo "结果: {$result}\n";
        } catch (Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    }
    
    echo "再见!\n";
}
