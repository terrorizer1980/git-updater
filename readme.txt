# Git Updater
Contributors: afragen
Donate link: https://thefragens.com/git-updater-donate
Tags: plugin, theme, language pack, updater
Requires at least: 5.2
Requires PHP: 5.6
Tested up to: 5.9
Stable tag: master
License: MIT

## Description

This plugin was designed to simply update any GitHub hosted WordPress plugin or theme. Currently, plugins or themes hosted on  Bitbucket, GitLab, Gitea, or Gist are also supported via additional API plugins. Additionally, self-hosted git servers are supported.

Your plugin or theme **must** contain a header in the style.css header or in the plugin's header denoting the location on GitHub. The format is as follows.

    GitHub Plugin URI: afragen/git-updater
    GitHub Plugin URI: https://github.com/afragen/git-updater

or

    GitHub Theme URI: afragen/test-child
    GitHub Theme URI: https://github.com/afragen/test-child

...where the above URI leads to the __owner/repository__ of your theme or plugin. The URI may be in the format `https://github.com/<owner>/<repo>` or the short format `<owner>/<repo>`. You do not need both. Only one Plugin or Theme URI is required. You **must not** include any extensions like `.git`.

### API Plugins

API plugins for Bitbucket, GitLab, Gitea, and Gist are available. API plugins are available for a one-click install from the **Add-Ons** tab.

* [Git Updater - Bitbucket](https://github.com/afragen/git-updater-bitbucket/releases/latest)
* [Git Updater - GitLab](https://github.com/afragen/git-updater-gitlab/releases/latest)
* [Git Updater - Gitea](https://github.com/afragen/git-updater-gitea/releases/latest)
* [Git Updater - Gist](https://github.com/afragen/git-updater-gist/releases/latest)

### Sponsor

You can [sponsor me on GitHub](https://github.com/sponsors/afragen) to help with continued development and support.

### Headers

The following headers are available for use depending upon your hosting source.

#### GitHub
* GitHub Plugin URI
* GitHub Theme URI
* GitHub Languages

## Frequently Asked Questions

#### Knowledge Base

[Comprehensive information regarding Git Updater is available in the Knowledge Base.](https://git-updater.com/knowledge-base)

#### Slack

We now have a [Slack team for Git Updater](https://git-updater.slack.com). Please [click here for an invite](https://git-updater.herokuapp.com). You will be automatically added to the _#general_ and _#support_ channels. Please take a look at other channels too.
