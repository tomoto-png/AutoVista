# AutoVist
## アプリケーション概要
- AutoVista（オートビスタ） は、車好きの方や、これから車の購入を考えている方のためのギャラリー共有アプリです。
- ユーザーはお気に入りの車の写真を投稿・共有でき、他のユーザーと車の魅力を分かち合うことができます。
- また、価格帯での絞り込み検索や、車の特徴を示すタグの追加機能により、理想の車を効率よく探すことができます。
- 「見て楽しむ」だけでなく、「選んで探す」ことにも活用できる実用的なサービスです。
## 使用技術スタック
- フレームワーク: Laravel
- データベース: Mysql
- フロントエンド関連: JQuery, JavaScript
- 開発: Docker, Git

## 主な機能
### トップページ
- 投稿の閲覧はログインなしでも可能です。
- 投稿機能（画像とタグ付きで投稿可能）
- 投稿一覧の表示（無限スクロール対応）
- 価格帯での絞り込みと検索機能
- おすすめ投稿の表示（「いいね」履歴のタグ集計による）

### マイページ
- ログインが必須です。
- プロフィール編集（名前・自己紹介・アバター）
- 自分の投稿一覧表示と編集・削除機能
- 「いいね」した投稿の一覧表示
- 無限スクロール対応

## 創意工夫
- 全体の配色は黒と白を基調としており、背景を黒にすることで画像がより際立ち、鮮明に見えることを目指しています。
- 	トップページではログインなしでも投稿を閲覧できるため、誰でも気軽に車の魅力に触れることができます。
- トップページにはおすすめ機能があります。各投稿にはタグが付けられており、ユーザーが「いいね」した投稿のタグ情報とその出現回数を記録しています。おすすめ機能は、これらの出現回数をもとに関連性の高い投稿を優先的に表示する仕組みになっており、ユーザーの興味にマッチした投稿をより効率よく閲覧することができます
- 投稿や編集時には、画像のプレビュー機能を実装しており、アップロード前に選択した画像を確認することができます。この機能により、誤った画像の投稿を防ぐことができ、ユーザーにとって安心して操作できる仕組みとなっています。
- トップページとマイページには、無限スクロール機能を搭載しており、ページ遷移なしで投稿をスムーズに閲覧できます。
## 実際のアプリケーション画像
<table>
    <tr>
        <td>
            新規登録ページ
        </td>
        <td>
            ログインページ
        </td>
    </tr>
    <tr>
        <td>
            <img width="1680" alt="Image" src="https://github.com/user-attachments/assets/32afd587-7d8f-4a00-b833-c9fad34fdce7" />
        </td>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/834f1865-04a9-44df-b4fe-afeaab6c1e18" />
        </td>
    </tr>
    <tr>
        <td>
            トップページ
        </td>
        <td>
            トップページ（投稿モーダル表示時）
        </td>
    </tr>
    <tr>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/d6252704-4bc1-4a67-9236-58bf92db4fd3" />
        </td>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/1dd02e19-3893-4de0-ad25-5c0565c18758" />
        </td>
    </tr>
    <tr>
        <td>
            マイページ(投稿一覧)
        </td>
        <td>
            マイページ(いいね一覧)
        </td>
    </tr>
    <tr>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/9ca521b4-e5b5-4f1c-99ba-171d04b00c04" />
        </td>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/6050ecf3-a677-4033-86c0-f23008c77538" />
        </td>
    </tr>
    <tr>
        <td>
            プロフィール編集ページ
        </td>
        <td>
            マイページ（投稿モーダル表示時）
        </td>
    </tr>
    <tr>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/1d2a4495-1304-412d-8d98-ffb5dec55cc0" />
        </td>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/9f74eae5-8f0e-417c-8a07-f8d4bb5315f5" />
        </td>
    </tr>
    <tr>
        <td>
            未ログイン時の表示ページ
        </td>
    </tr>
    <tr>
        <td>
            <img width="1440" alt="Image" src="https://github.com/user-attachments/assets/b905c0ec-5b11-4f1e-969f-3e6167657744" />
        </td>
    </tr>
</table>
