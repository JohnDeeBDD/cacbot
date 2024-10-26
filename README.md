# Cacbot

Cacbot is a WordPress plugin that allows interaction with artificially intelligent agents directly on a WordPress site using the native comments system to record chats. It is written entirely in PHP and works on any site that has WordPress. Cacbots can transform and generate text, create images, execute functions, and perform actions.

It is intended for developers and admins who want to build WordPress applications that use A.I.. Try it for free.

### Getting Started

1. Download the ZIP file. Upload `cacbot.zip` to your WordPress site. Activate the plugin.
2. Click the **Cacbot Conversations** tab in your WordPress admin backend. Click **Add New**.
3. Give a name to the conversation (e.g., "test"). Set the conversation visibility to **Private**. Click **Publish**. View the conversation on the frontend.
4. Type something into the comment section. When you publish a comment, the Cacbot Assistant will respond as if it is being spoken to. The response will be saved as a comment in the database.

### Features

| Feature | Description |
| ------- | ----------- |
| `Cacbot` user role | A WordPress user who is an artificially intelligent agent. Responds to comments on Cacbot Conversation CPTs. |
| [`Fetch Image`](https://cacbot.com/features/fetch-image/) | Cacbots can directly generate images and save them to your WordPress media library. |
| [`Cacbot Conversation`](https://cacbot.com/features/cpt/) custom post type | Where conversations with Cacbots happen in the comment sections. |
| [`System Instructions`](https://cacbot.com/features/system-instructions/) post meta | LLMs expect system instructions for chat completions. Default is: "You are a helpful assistant." |
| [`Interlocutor`](https://cacbot.com/features/cacbot-interlocutor/) post meta | User ID of the user who is conversing with the post author. One or both users must be a Cacbot. |
| `Cacbot Conversation` post author | User ID of the user who is conversing with the interlocutor. One or both users must be a Cacbot. |
| Anchor Post | An anchor post generates separate Cacbot conversations for each user, and appends that chat to the post when the user views it. Regular WordPress posts and pages can be designated as Cacbot Anchor posts. |
| Navigation | Cacbots can navigate the user through the site based on their responses. | 
