Tag Extension for [Mecha](https://github.com/mecha-cms/mecha)
=============================================================

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/x.tag?color=%23444&style=for-the-badge)

This extension activates the tag feature by utilizing the `kind` property of the page. This extension will also add several new routes such as `http://127.0.0.1/blog/tag/:tag/1` on every page to allow users to list all pages in the current folder by a tag.

---

Release Notes
-------------

### 2.0.0

 - [x] Updated for Mecha 3.0.0.

### 1.11.2

 - Added `parent` property to `Tag` and `Tags` class to store the parent page instance.
 - Changed `page` property value in `Tag` and `Tags` class instance to the current page instance.

### 1.11.0

 - Added `page` property to the `Tag` class as well to store the parent page instance.
 - Preferred `$tag->link` over `$tag->url`.

### 1.10.2

 - [@mecha-cms/mecha#96](https://github.com/mecha-cms/mecha/issues/96)

### 1.10.1

 - Added page offset at the end of the tag URL.
 - Small bug fixes.

### 1.10.0

 - Updated for Mecha 2.5.0.

### 1.9.6

 - Updated for Mecha 2.4.0.

### 1.9.5

 - Added `page` property to `Tags` to store the parent page instance.

### 1.9.4

 - Updated for Mecha 2.2.2.

### 1.9.3

 - Fixed bug for tags page.
 - Fixed missing “Tag” title.

### 1.9.2

 - Added `$tag` variable to global.