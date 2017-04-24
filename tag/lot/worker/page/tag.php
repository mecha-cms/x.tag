<form id="form.m.editor" action="" method="post">
  <aside class="s">
    <section class="s-author">
      <h3><?php echo $language->author; ?></h3>
      <p>
<?php

$__authors = [];
$__select = $__page[0]->author;
foreach (g(ENGINE . DS . 'log' . DS . 'user', 'page') as $__v) {
    $__v = new User(Path::N($__v));
    $__k = $__v->key;
    if ($__user->status !== 1 && $__k !== $__user->key) continue;
    $__authors[User::ID . $__k] = $__v->author;
}
echo Form::select('author', $__user->status !== 1 && $__sgr !== 's' ? [User::ID . $__page[0]->author => $__page[1]->author] : $__authors, $__select, ['classes' => ['select', 'block'], 'id' => 'f-author']);

?>
      </p>
    </section>
    <?php if ($__kins[0]): ?>
    <section class="s-kin">
      <h3><?php echo $language->{count($__kins[0]) === 1 ? 'kin' : 'kins'}; ?></h3>
      <ul>
        <?php foreach ($__kins[0] as $k => $v): ?>
        <li><?php echo HTML::a($__kins[1][$k]->title, $v->url); ?></li>
        <?php endforeach; ?>
        <li><?php echo HTML::a('&#x2795;', $__state->path . '/::s::/' . Path::D($__path), false, ['title' => $language->add]); ?><?php echo $__is_has_step_kin ? ' ' . HTML::a('&#x22EF;', $__state->path . '/::g::/' . Path::D($__path) . '/2', false, ['title' => $language->more]) : ""; ?></li>
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
    'pattern' => '^[a-z\\d-]+$',
    'required' => true
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
        'classes' => ['button', 'set', 'x-' . $__x],
        'id' => 'f-state:' . $__x
    ]);
}

foreach ([
    'page' => $language->publish,
    'draft' => $language->save,
    'trash' => $__sgr === 's' ? false : $language->delete
] as $__k => $__v) {
    if (!$__v || $__x === $__k) continue;
    echo ' ' . Form::submit('x', $__k, $__v, [
        'classes' => ['button', 'set', 'x-' . $__k],
        'id' => 'f-state:' . $__k
    ]);
}

?>
      </span>
    </p>
    <?php echo Form::hidden('token', $__token); ?>
  </main>
  <aside class="s">
    <section class="s-id">
      <h3><?php echo $language->id; ?></h3>
      <?php

$__i = 0;
foreach (glob(TAG . DS . '*' . DS . 'id.data', GLOB_NOSORT) as $__v) {
    $__id = (int) file_get_contents($__v);
    if ($__id > $__i) $__i = $__id;
}
++$__i;

      ?>
      <p><?php echo Form::text('id', $__sgr === 's' ? $__i : $__page[0]->id, $__i, ['classes' => ['input', 'block'], 'id' => 'f-id', 'readonly' => true]); ?></p>
    </section>
  </aside>
</form>