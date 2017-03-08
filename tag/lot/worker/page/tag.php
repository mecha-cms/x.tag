<form id="form.m.editor" action="" method="post">
  <aside class="s">
    <section class="s-author">
      <h3><?php echo $language->author; ?></h3>
      <p><?php echo Form::text('author', $__page[0]->author); ?></p>
    </section>
    <?php if ($__kins[0]): ?>
    <section class="s-kin">
      <h3><?php echo $language->{count($__kins[0]) === 1 ? 'kin' : 'kins'}; ?></h3>
      <ul>
        <?php foreach ($__kins[0] as $k => $v): ?>
        <li><?php echo HTML::a($__kins[1][$k]->title, $v->url); ?></li>
        <?php endforeach; ?>
        <?php if ($__is_kin_has_step): ?>
        <li><?php echo HTML::a('&#x2026;', $__state->path . '/::g::/' . Path::D($__path) . '/2', false, ['title' => $language->more]); ?></li>
        <?php endif; ?>
      </ul>
    </section>
    <?php endif; ?>
  </aside>
  <main class="m">
    <?php echo $__message; ?>
    <fieldset>
      <legend><?php echo $language->editor; ?></legend>
      <p class="f f-title expand">
        <label for="f-title"><?php echo $language->title; ?></label>
        <span>
<?php echo Form::text('title', $__page[0]->title, $language->f_title, [
    'classes' => ['input', 'block'],
    'id' => 'f-title',
    'data' => ['slug-i' => 'title']
]); ?>
        </span>
      </p>
      <p class="f f-slug expand">
        <label for="f-slug"><?php echo $language->slug; ?></label>
        <span>
<?php echo Form::text('slug', $__page[0]->slug, To::slug($language->f_title), [
    'classes' => ['input', 'block'],
    'id' => 'f-slug',
    'data' => ['slug-o' => 'title'],
    'pattern' => '^[a-z\\d-]+$'
]); ?>
        </span>
      </p>
      <div class="f f-content expand p">
        <label for="f-content"><?php echo $language->content; ?></label>
        <div>
<?php echo Form::textarea('content', $__page[0]->content, $language->f_content, [
    'classes' => ['textarea', 'block', 'expand', 'code', 'editor'],
    'id' => 'f-content',
    'data' => ['type' => $__page[0]->type]
]); ?>
        </div>
      </div>
      <p class="f f-type">
        <label for="f-type"><?php echo $language->type; ?></label>
        <span>
<?php $__types = a(Config::get('panel.f.page.types')); ?>
<?php asort($__types); ?>
<?php echo Form::select('type', $__types, $__page[0]->type, [
    'classes' => ['select'],
    'id' => 'f-type'
]); ?>
        </span>
      </p>
      <div class="f f-description p">
        <label for="f-description"><?php echo $language->description; ?></label>
        <div>
<?php echo Form::textarea('description', $__page[0]->description, $language->f_description($language->tag), [
    'classes' => ['textarea', 'block'],
    'id' => 'f-description'
]); ?>
        </div>
      </div>
    </fieldset>
    <p class="f f-state expand">
      <label for="f-state"><?php echo $language->state; ?></label>
      <span>
<?php

$__x = $__page[0]->state;

if ($__sgr !== 's') {
    echo Form::submit('x', $__x, $language->update, [
        'classes' => ['button', 'x-' . $__x],
        'id' => 'f-state:' . $__x
    ]);
}

foreach ([
    'page' => $language->publish,
    'draft' => $language->save,
    'trash' => $language->delete
] as $k => $v) {
    if ($__x === $k) continue;
    echo ' ' . Form::submit('x', $k, $v, [
        'classes' => ['button', 'x-' . $k],
        'id' => 'f-state:' . $k
    ]);
}

?>
      </span>
    </p>
    <?php echo Form::token(); ?>
  </main>
  <aside class="s"></aside>
</form>