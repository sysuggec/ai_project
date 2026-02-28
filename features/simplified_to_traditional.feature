Feature: 简体字转繁体字转换器
  As a 用户
  I want to 将简体中文字符串转换为繁体中文
  So that 我可以在需要繁体中文的场景中使用

  Scenario: 转换单个简体字
    Given 我有一个简繁转换器
    When 我将 "国" 转换为繁体
    Then 我应该得到 "國"

  Scenario: 转换简体字符串
    Given 我有一个简繁转换器
    When 我将 "中华人民共和国" 转换为繁体
    Then 我应该得到 "中華人民共和國"

  Scenario: 转换包含非中文字符的字符串
    Given 我有一个简繁转换器
    When 我将 "Hello 中国!" 转换为繁体
    Then 我应该得到 "Hello 中國!"

  Scenario: 转换空字符串
    Given 我有一个简繁转换器
    When 我将 "" 转换为繁体
    Then 我应该得到 ""

  Scenario: 转换已经是繁体的字符串
    Given 我有一个简繁转换器
    When 我将 "繁體字" 转换为繁体
    Then 我应该得到 "繁體字"

  Scenario Outline: 转换常用词汇
    Given 我有一个简繁转换器
    When 我将 "<简体>" 转换为繁体
    Then 我应该得到 "<繁体>"

    Examples:
      | 简体       | 繁体       |
      | 软件       | 軟體       |
      | 网络       | 網絡       |
      | 数据       | 數據       |
      | 电脑       | 電腦       |
      | 信息       | 信息       |
