## 代码审查能力评测
请按照下面的步骤执行我的命令：
1.等待我输入审查文件的路径
2.分别让deepseek glm hunyuan kimi 和minimax 使用code-reviewer skill 对审查文件进行代码审查（用中文输出结果，并保存到{审查文件名}/report目录，文件以当前使用的model名称命名）
3.等待所有subaget完成审查和保存文件
4.分别让deepseek glm hunyuan kimi 和minimax 对比 {审查文件名}/report目录下的所有文件(文件名称对应大模型的名称)，并参考review/bug.md文件，统计标准结果在各自模型中的检测覆盖情况。输出一个详细的模型代码审查能力的报告，报告以当前使用的模型名称保存到{审查文件名}/report-stats目录。
5.对比报告都生成好后，综合对比报告的内容，给出评审能力排行榜,结果保存在{审查文件名}/rank.md



