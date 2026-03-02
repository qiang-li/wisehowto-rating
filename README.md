# WiseHowTo Rating Widget

> 为 [WiseHowTo.com](https://wisehowto.com) AI 工具目录提供用户评分功能的 WordPress 插件。

---

## 项目背景

WiseHowTo.com 是一个 AI 工具目录与教程网站，帮助用户发现并选择适合自己的 AI 工具。

用户在浏览一个 AI 工具详情页时，最需要的参考信息之一就是**其他用户的真实评价**。本插件解决的核心问题是：

> "这个 AI 工具到底好不好用？别人怎么看？"

---

## 面向谁

| 角色 | 诉求 |
|------|------|
| WiseHowTo 普通访客 | 快速看到工具的综合评分，辅助决策 |
| WiseHowTo 注册用户（后期）| 提交自己的评分，贡献社区数据 |
| 网站管理员 | 以 Shortcode 方式灵活嵌入任意页面 |

---

## 解决什么问题

- **冷启动信任问题**：新工具上线后缺乏口碑背书，评分系统让用户声音可见
- **决策摩擦**：用户在多个工具间难以选择，聚合评分提供直观参考
- **内容互动性不足**：纯展示型目录缺乏用户参与，评分是最低门槛的互动方式

---

## 功能概览

- ⭐ 1-5 星交互式评分组件
- 📊 实时展示平均分与评分总数
- 🛡️ Cookie + IP 双重防刷机制
- 🔌 Shortcode 一行嵌入：`[wisehowto_rating tool_id="123"]`

---

## 技术规格

详见 [SPEC.md](./SPEC.md)

---

## 开发方法论

本项目采用 **Harness Engineering** 方法开发：

- AI 负责生成代码骨架与实现
- 人工负责审查、定义测试、把控边界
- 测试先行，每个功能以 PHPUnit 测试覆盖后方可合并

---

## 快速开始

```bash
# 克隆项目
git clone https://github.com/yourname/wisehowto-rating.git

# 放入 WordPress 插件目录
cp -r wisehowto-rating /path/to/wp-content/plugins/

# 在 WordPress 后台激活插件，然后在任意页面插入 Shortcode
[wisehowto_rating tool_id="123"]
```

---

## 项目状态

| 阶段 | 状态 |
|------|------|
| SPEC 文档 | ✅ 完成 |
| 脚手架生成 | 🔜 进行中 |
| 测试框架搭建 | ⏳ 待开始 |
| MVP 功能完成 | ⏳ 待开始 |

---

## License

MIT
