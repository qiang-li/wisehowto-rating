# WiseHowTo Rating Widget — SPEC.md

## 插件基本信息

```
插件名：WiseHowTo Rating Widget
版本：0.1.0（MVP）
目标：为 WiseHowTo 的 AI 工具详情页提供用户评分功能，并聚合展示平均分
```

---

## 功能边界（MVP 范围）

### 核心功能（必须有）

- 用户对 AI 工具进行 1-5 星评分
- 每个用户对每个工具只能评一次（用 cookie + IP 双重防刷）
- 展示当前工具的平均分和评分总数
- Shortcode 方式嵌入：`[wisehowto_rating tool_id="123"]`

### 明确不做（MVP 外）

- 用户登录系统（后期再接）
- 评分维度细分（如易用性 / 功能性分开打分）
- 后台评分管理界面

---

## 数据结构

```sql
-- 自定义表：存储每条评分记录
wp_wisehowto_ratings (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  tool_id     INT NOT NULL,         -- 对应 WP post ID
  rating      TINYINT NOT NULL,     -- 1~5
  user_ip     VARCHAR(45),
  cookie_hash VARCHAR(64),
  created_at  DATETIME DEFAULT NOW()
)
```

---

## 输入 / 输出定义

| 场景 | 输入 | 输出 |
|------|------|------|
| 页面加载 | `tool_id` | 渲染星级 UI + 当前平均分 |
| 用户点击提交评分 | `tool_id` + `rating(1-5)` | JSON `{success, avg_rating, total}` |
| 重复评分 | `tool_id` + 已评过的用户 | JSON `{success: false, message: "已评分"}` |

---

## 边界条件（测试的核心依据）

- `rating` 必须是 1~5 的整数，否则返回 400 错误
- `tool_id` 必须是有效的 WP post ID
- 同一 IP + cookie 组合在同一 `tool_id` 下只允许一条记录
- AJAX 请求需验证 WordPress nonce，防 CSRF

---

## 目录结构（AI 脚手架生成目标）

```
wisehowto-rating/
├── wisehowto-rating.php        # 插件主入口
├── includes/
│   ├── class-rating-db.php     # 数据库操作
│   ├── class-rating-ajax.php   # AJAX 处理
│   └── class-rating-widget.php # Shortcode 渲染
├── assets/
│   ├── rating.js               # 前端交互
│   └── rating.css              # 星级样式
└── tests/
    ├── test-rating-db.php
    └── test-rating-ajax.php
```

---

## 开发阶段规划

| 阶段 | 内容 | 产出 |
|------|------|------|
| 阶段 1 | 需求文档化 | 本 SPEC.md |
| 阶段 2 | AI 脚手架生成 | 插件目录骨架 + 主文件 |
| 阶段 3 | 测试先行 | PHPUnit 测试用例 |
| 阶段 4 | 功能迭代 | 完整可用插件 |
| 阶段 5 | 反思与文档 | CHANGELOG + 注释 |

---

## 工具栈

| 用途 | 工具 |
|------|------|
| AI 辅助编码 | Claude / Cursor |
| 本地开发环境 | LocalWP |
| 测试框架 | PHPUnit + WP_Mock |
| 版本控制 + CI | GitHub + GitHub Actions |

---

*最后更新：2026-03-01*
