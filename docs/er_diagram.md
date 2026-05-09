# ER図（データベース設計）

## テーブル構成

```mermaid
erDiagram
    users ||--o{ children : "has"
    children ||--o{ tasks : "has"
    children ||--o{ task_logs : "has"
    children ||--o{ diaries : "writes"
    children ||--o{ reward_logs : "exchanges"
    tasks ||--o{ task_logs : "logged in"
    diaries ||--o{ diary_replies : "receives"
    rewards ||--o{ reward_logs : "exchanged as"

    users {
        int id PK
        string username
        string password_hash
        datetime created_at
        datetime deleted_at
    }

    children {
        int id PK
        int user_id FK
        string name
        int total_points
        datetime deleted_at
    }

    tasks {
        int id PK
        int child_id FK
        string title
        int points
        datetime deleted_at
    }

    task_logs {
        int id PK
        int task_id FK
        date completed_date
        datetime deleted_at
    }

    diaries {
        int id PK
        int child_id FK
        text content
        int body_score
        int mind_score
        date diary_date
        datetime deleted_at
    }

    diary_replies {
        int id PK
        int diary_id FK
        text content
        datetime deleted_at
    }

    rewards {
        int id PK
        string name
        int required_points
        datetime deleted_at
    }

    reward_logs {
        int id PK
        int child_id FK
        int reward_id FK
        datetime exchanged_at
        datetime deleted_at
    }
```

## 設計のポイント

### 1. マルチテナント設計
`users`テーブルで家族単位のアカウントを管理。`children`テーブルに`user_id`を持たせることで、複数家族が同じアプリを安全に使用できます。

認可チェックにより、URL直打ちによる他の家族のデータへのアクセスを防止しています。

```sql
-- 自分の家族の子どもだけ取得
SELECT id, name FROM children 
WHERE deleted_at IS NULL AND user_id = ?
```

### 2. 論理削除の採用
全テーブルに `deleted_at` カラムを実装。データを物理的に削除せず、履歴を保持することで：
- 誤削除からの復元が可能
- 過去のデータ分析が可能
- 子どもの成長記録を永続的に保存

### 3. 1対多の関係
中間テーブルを使わず、直接外部キーで関連付け：
- `users` ← `children`（1アカウントが複数の子どもを持つ）
- `children` ← `tasks`（1人の子が複数のタスクを持つ）
- `children` ← `diaries`（1人の子が複数の日記を書く）
- `tasks` ← `task_logs`（1つのタスクが複数回達成される）
- `diaries` ← `diary_replies`（1つの日記に複数の返信）

### 4. 1日1回制限の実現
`task_logs` テーブルの `completed_date` カラムに日付を記録。
```sql
SELECT * FROM task_logs 
WHERE task_id = ? AND completed_date = CURDATE()
```
で同日の重複達成をチェック。

### 5. ポイント管理
- `children.total_points`：累計ポイントを保持
- タスク達成時にトランザクションで更新：
```sql
BEGIN TRANSACTION
  INSERT INTO task_logs (task_id, completed_date) VALUES (?, CURDATE());
  UPDATE children SET total_points = total_points + ? WHERE id = ?;
COMMIT
```

### 6. 外部キー制約
- `ON DELETE CASCADE`ではなく、論理削除で対応
- 参照整合性を保ちながら履歴を保持
