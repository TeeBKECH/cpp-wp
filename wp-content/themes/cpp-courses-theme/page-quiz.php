<?php
/**
 * Template Name: QUIZ
 *
 * Intro from page content; questions from related quiz CPT posts (quiz_items).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    break;
endwhile;

$page_id = get_queried_object_id();
$question_ids = function_exists('cpp_quiz_get_ordered_question_ids') ? cpp_quiz_get_ordered_question_ids($page_id) : array();
$extra_link = function_exists('get_field') ? get_field('quiz_rosvgard_link', $page_id) : null;
$extra_url = is_array($extra_link) && !empty($extra_link['url']) ? (string) $extra_link['url'] : '';
$extra_title = is_array($extra_link) && !empty($extra_link['title']) ? (string) $extra_link['title'] : '';
if ($extra_title === '' && $extra_url !== '') {
    $extra_title = $extra_url;
}

$extra_target = is_array($extra_link) && !empty($extra_link['target']) ? (string) $extra_link['target'] : '_self';

$results_title = function_exists('get_field') ? (string) get_field('quiz_results_title', $page_id) : '';
if ($results_title === '') {
    $results_title = __('Результаты', 'cpp-courses-theme');
}
$results_text = function_exists('get_field') ? (string) get_field('quiz_results_text', $page_id) : '';

$cf7_form_post = cpp_courses_get_option('cpp_cf7_form_post', null);
$cf7_html = '';
if (!empty($cf7_form_post)) {
    $cf7_html = do_shortcode('[contact-form-7 id="' . (int) $cf7_form_post . '"]');
}

$nonce = wp_create_nonce('cpp_quiz');
$ajax_url = admin_url('admin-ajax.php');
$has_quiz = count($question_ids) > 0;
?>
<main class="main main--test-intro" id="cpp-quiz-main" data-phase="intro">
    <section class="section section--page-intro" id="cpp-quiz-page-intro">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title"><?php the_title(); ?></h1>
            </div>
        </div>
    </section>

    <section class="section section--test-intro cpp-quiz-panel" id="cpp-quiz-intro-section">
        <div class="container">
            <div class="test-intro">
                <div class="test-intro_text entry-content" id="cpp-quiz-intro-text">
                    <?php the_content(); ?>
                </div>
                <div class="test-intro_actions" id="cpp-quiz-intro-actions">
                    <?php if ($has_quiz) : ?>
                        <button type="button" class="button button--filled button--lg" id="cpp-quiz-start">
                            <span class="button_text"><?php esc_html_e('Приступить →', 'cpp-courses-theme'); ?></span>
                        </button>
                    <?php else : ?>
                        <p class="page-intro_desc"><?php esc_html_e('Вопросы для теста не выбраны. Укажите их в полях страницы.', 'cpp-courses-theme'); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($has_quiz) : ?>
                    <div class="test-quiz cpp-quiz-panel cpp-quiz-panel--hidden" id="cpp-quiz-stage" hidden>
                        <form class="test-quiz_form" id="cpp-quiz-form" action="#" method="get" onsubmit="return false;">
                            <div class="test-quiz_question" id="cpp-quiz-question-wrap">
                                <div class="test-quiz_question-text entry-content" id="cpp-quiz-question-body"></div>
                            </div>
                            <div class="test-quiz_options" id="cpp-quiz-options"></div>
                            <div class="test-quiz_actions">
                                <span class="test-quiz_progress" id="cpp-quiz-progress" aria-live="polite"></span>
                                <button class="button button--filled button--md" type="button" id="cpp-quiz-primary">
                                    <span class="button_text" id="cpp-quiz-primary-label"><?php esc_html_e('Далее →', 'cpp-courses-theme'); ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($extra_url !== '') : ?>
                <div class="test-intro_footer" id="cpp-quiz-intro-footer">
                    <a class="test-intro_link wave-link" href="<?php echo esc_url($extra_url); ?>" target="<?php echo esc_attr($extra_target); ?>"><?php echo esc_html($extra_title); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($has_quiz) : ?>
        <section class="section section--test-quiz cpp-quiz-panel cpp-quiz-panel--hidden" id="cpp-quiz-results-section" hidden>
            <div class="container">
                <div class="test-quiz">
                    <div class="page-intro" style="margin-bottom: 1.5rem;">
                        <h2 class="page-intro_title" id="cpp-quiz-results-title"><?php echo esc_html($results_title); ?></h2>
                        <?php if ($results_text !== '') : ?>
                            <p class="page-intro_desc"><?php echo nl2br(esc_html($results_text)); ?></p>
                        <?php endif; ?>
                        <p class="page-intro_desc" id="cpp-quiz-results-score"></p>
                        <ol class="cpp-quiz-review" id="cpp-quiz-review" aria-label="<?php echo esc_attr__('Разбор ответов', 'cpp-courses-theme'); ?>"></ol>
                        <div class="cpp-quiz-results-actions">
                            <button type="button" class="button button--outline button--md" id="cpp-quiz-restart">
                                <span class="button_text"><?php esc_html_e('Пройти ещё раз', 'cpp-courses-theme'); ?></span>
                            </button>
                        </div>
                    </div>
                    <?php if ($cf7_html !== '') : ?>
                        <div class="test-quiz_form cpp-quiz-results-form">
                            <?php echo $cf7_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($extra_url !== '') : ?>
                        <div class="test-quiz_footer">
                            <a class="test-quiz_link wave-link" href="<?php echo esc_url($extra_url); ?>" target="<?php echo esc_attr($extra_target); ?>"><?php echo esc_html($extra_title); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<script>
(function () {
  const cfg = {
    pageId: <?php echo (int) $page_id; ?>,
    ajaxUrl: <?php echo wp_json_encode($ajax_url); ?>,
    nonce: <?php echo wp_json_encode($nonce); ?>,
    storageKey: 'cpp_quiz_<?php echo (int) $page_id; ?>_v3',
    totalHint: <?php echo (int) count($question_ids); ?>,
    labelNext: <?php echo wp_json_encode(__('Далее →', 'cpp-courses-theme'), JSON_UNESCAPED_UNICODE); ?>,
    labelFinish: <?php echo wp_json_encode(__('Показать результаты', 'cpp-courses-theme'), JSON_UNESCAPED_UNICODE); ?>,
    labelYourAnswer: <?php echo wp_json_encode(__('Ваш ответ:', 'cpp-courses-theme'), JSON_UNESCAPED_UNICODE); ?>
  };

  const main = document.getElementById('cpp-quiz-main');
  const introSec = document.getElementById('cpp-quiz-intro-section');
  const introText = document.getElementById('cpp-quiz-intro-text');
  const introActions = document.getElementById('cpp-quiz-intro-actions');
  const introFooter = document.getElementById('cpp-quiz-intro-footer');
  const stage = document.getElementById('cpp-quiz-stage');
  const resSec = document.getElementById('cpp-quiz-results-section');
  const startBtn = document.getElementById('cpp-quiz-start');
  const form = document.getElementById('cpp-quiz-form');
  const qBody = document.getElementById('cpp-quiz-question-body');
  const opts = document.getElementById('cpp-quiz-options');
  const primaryBtn = document.getElementById('cpp-quiz-primary');
  const primaryLabel = document.getElementById('cpp-quiz-primary-label');
  const progressEl = document.getElementById('cpp-quiz-progress');
  const scoreEl = document.getElementById('cpp-quiz-results-score');
  const reviewEl = document.getElementById('cpp-quiz-review');
  const restartBtn = document.getElementById('cpp-quiz-restart');

  if (!main || !stage || !form || !qBody || !opts || !primaryBtn || !primaryLabel) return;

  function setPanelHidden(el, hidden) {
    if (!el) return;
    if (hidden) {
      el.hidden = true;
      el.classList.add('cpp-quiz-panel--hidden');
    } else {
      el.hidden = false;
      el.classList.remove('cpp-quiz-panel--hidden');
    }
  }

  let state = {
    started: false,
    index: 0,
    total: cfg.totalHint || 0,
    correctKey: [],
    userAnswers: [],
    snapshots: []
  };

  function loadState() {
    try {
      const raw = localStorage.getItem(cfg.storageKey);
      if (!raw) return;
      const o = JSON.parse(raw);
      if (o && typeof o === 'object') {
        state.started = !!o.started;
        state.index = typeof o.index === 'number' ? o.index : 0;
        state.correctKey = Array.isArray(o.correctKey) ? o.correctKey : [];
        state.userAnswers = Array.isArray(o.userAnswers) ? o.userAnswers : [];
        state.total = typeof o.total === 'number' ? o.total : state.total;
        state.snapshots = Array.isArray(o.snapshots) ? o.snapshots : [];
      }
    } catch (e) {}
  }

  function saveState() {
    try {
      localStorage.setItem(
        cfg.storageKey,
        JSON.stringify({
          started: state.started,
          index: state.index,
          total: state.total,
          correctKey: state.correctKey,
          userAnswers: state.userAnswers,
          snapshots: state.snapshots
        })
      );
    } catch (e) {}
  }

  function setPagePhase(phase) {
    main.dataset.phase = phase;
    if (phase === 'quiz') {
      main.classList.remove('main--test-intro');
      main.classList.add('main--test-quiz');
    } else if (phase === 'intro') {
      main.classList.add('main--test-intro');
      main.classList.remove('main--test-quiz');
    } else if (phase === 'results') {
      main.classList.remove('main--test-intro');
      main.classList.add('main--test-quiz');
    }
  }

  function renderProgress() {
    if (!progressEl) return;
    const cur = state.index + 1;
    const t = state.total || cfg.totalHint || '?';
    progressEl.textContent = cur + '/' + t;
  }

  function updatePrimaryButton() {
    const last = state.total > 0 && state.index >= state.total - 1;
    primaryLabel.textContent = last ? cfg.labelFinish : cfg.labelNext;
    primaryBtn.classList.toggle('button--primary', last);
    primaryBtn.classList.toggle('button--filled', !last);
    renderProgress();
  }

  function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function renderQuestion(data) {
    qBody.innerHTML = data.question_html || '';
    opts.innerHTML = '';
    const type = data.input_type === 'checkbox' ? 'checkbox' : 'radio';
    const name = 'cpp_q_' + data.index;
    const saved = state.userAnswers[data.index];

    (data.answers || []).forEach(function (a) {
      const i = a.i;
      const checked =
        type === 'radio'
          ? Array.isArray(saved) && saved.length === 1 && String(saved[0]) === String(i)
          : Array.isArray(saved) && saved.map(String).indexOf(String(i)) !== -1;

      if (type === 'radio') {
        const lab = document.createElement('label');
        lab.className = 'radio';
        lab.innerHTML =
          '<input class="radio_input" type="radio" name="' +
          escapeHtml(name) +
          '" value="' +
          escapeHtml(String(i)) +
          '" ' +
          (checked ? 'checked ' : '') +
          '/>' +
          '<span class="radio_box" aria-hidden="true"></span>' +
          '<span class="radio_text"><span>' +
          escapeHtml(a.text) +
          '</span></span>';
        opts.appendChild(lab);
      } else {
        const lab = document.createElement('label');
        lab.className = 'checkbox checkbox--option';
        lab.innerHTML =
          '<input class="checkbox_input" type="checkbox" name="' +
          escapeHtml(name + '[]') +
          '" value="' +
          escapeHtml(String(i)) +
          '" ' +
          (checked ? 'checked ' : '') +
          '/>' +
          '<div class="checkbox_box" aria-hidden="true"></div>' +
          '<div class="checkbox_text"><span>' +
          escapeHtml(a.text) +
          '</span></div>';
        opts.appendChild(lab);
      }
    });

    state.correctKey[data.index] = (data.correct_indices || []).slice().sort(function (a, b) {
      return a - b;
    });
    state.total = data.total;
    state.snapshots[data.index] = {
      question_html: data.question_html || '',
      answers: (data.answers || []).map(function (a) {
        return { i: a.i, text: a.text };
      }),
      correct_indices: (data.correct_indices || []).slice(),
      input_type: data.input_type
    };
    updatePrimaryButton();
    saveState();
  }

  function readCurrentSelection() {
    const radios = opts.querySelectorAll('input[type="radio"]:checked');
    if (radios.length) {
      return [parseInt(radios[0].value, 10)];
    }
    const cbs = opts.querySelectorAll('input[type="checkbox"]:checked');
    const out = [];
    cbs.forEach(function (c) {
      out.push(parseInt(c.value, 10));
    });
    return out.sort(function (a, b) {
      return a - b;
    });
  }

  function arraysEqual(a, b) {
    if (!Array.isArray(a) || !Array.isArray(b) || a.length !== b.length) return false;
    for (let k = 0; k < a.length; k++) {
      if (String(a[k]) !== String(b[k])) return false;
    }
    return true;
  }

  function fetchQuestion(idx) {
    const body = new URLSearchParams();
    body.set('action', 'cpp_quiz_fetch_question');
    body.set('nonce', cfg.nonce);
    body.set('page_id', String(cfg.pageId));
    body.set('index', String(idx));
    return fetch(cfg.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
      credentials: 'same-origin'
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (json) {
        if (!json || !json.success) throw new Error('fetch_failed');
        return json.data;
      });
  }

  function showIntro() {
    setPagePhase('intro');
    if (introText) introText.hidden = false;
    if (introActions) introActions.hidden = false;
    setPanelHidden(stage, true);
    if (introFooter) introFooter.hidden = false;
    setPanelHidden(introSec, false);
    setPanelHidden(resSec, true);
    if (reviewEl) reviewEl.innerHTML = '';
  }

  function showQuiz() {
    setPagePhase('quiz');
    if (introText) introText.hidden = true;
    if (introActions) introActions.hidden = true;
    setPanelHidden(stage, false);
    if (introFooter) introFooter.hidden = true;
    setPanelHidden(introSec, false);
    setPanelHidden(resSec, true);
  }

  function renderResultsReview() {
    if (!reviewEl) return;
    reviewEl.innerHTML = '';
    for (let i = 0; i < state.total; i++) {
      const snap = state.snapshots[i];
      const key = Array.isArray(state.correctKey[i]) ? state.correctKey[i] : [];
      const user = Array.isArray(state.userAnswers[i]) ? state.userAnswers[i] : [];
      const ks = key.slice().map(String).sort();
      const us = user.slice().map(String).sort();
      const ok = arraysEqual(ks, us);

      const texts = [];
      if (snap && Array.isArray(snap.answers)) {
        user.forEach(function (ui) {
          const found = snap.answers.find(function (a) {
            return String(a.i) === String(ui);
          });
          texts.push(found && found.text ? String(found.text) : String(ui));
        });
      }
      const lineText = texts.length ? texts.join(' / ') : '—';

      const li = document.createElement('li');
      li.className = 'cpp-quiz-review__item';

      const qWrap = document.createElement('div');
      qWrap.className = 'cpp-quiz-review__question entry-content';
      qWrap.innerHTML = snap && snap.question_html ? snap.question_html : '';

      const p = document.createElement('p');
      p.className = 'cpp-quiz-review__answer-line';
      const lab = document.createElement('span');
      lab.className = 'cpp-quiz-review__label';
      lab.textContent = cfg.labelYourAnswer + ' ';
      const val = document.createElement('span');
      val.className = ok ? 'cpp-quiz-review__value--ok' : 'cpp-quiz-review__value--bad';
      val.textContent = lineText;
      p.appendChild(lab);
      p.appendChild(val);

      li.appendChild(qWrap);
      li.appendChild(p);
      reviewEl.appendChild(li);
    }
  }

  function showResults() {
    let correct = 0;
    for (let i = 0; i < state.total; i++) {
      const key = Array.isArray(state.correctKey[i]) ? state.correctKey[i] : [];
      const user = Array.isArray(state.userAnswers[i]) ? state.userAnswers[i] : [];
      const ks = key.slice().map(String).sort();
      const us = user.slice().map(String).sort();
      if (arraysEqual(ks, us)) {
        correct++;
      }
    }
    if (scoreEl) {
      scoreEl.textContent =
        <?php echo wp_json_encode(__('Верных ответов:', 'cpp-courses-theme'), JSON_UNESCAPED_UNICODE); ?> +
        ' ' +
        correct +
        ' ' +
        <?php echo wp_json_encode(__('из', 'cpp-courses-theme'), JSON_UNESCAPED_UNICODE); ?> +
        ' ' +
        state.total;
    }
    renderResultsReview();
    setPagePhase('results');
    setPanelHidden(introSec, true);
    setPanelHidden(resSec, false);
    try {
      localStorage.removeItem(cfg.storageKey);
    } catch (e) {}
  }

  function onPrimaryClick() {
    const last = state.total > 0 && state.index >= state.total - 1;
    if (last) {
      const sel = readCurrentSelection();
      state.userAnswers[state.index] = sel;
      saveState();
      showResults();
      return;
    }
    const sel = readCurrentSelection();
    state.userAnswers[state.index] = sel;
    saveState();
    if (state.index < state.total - 1) {
      state.index++;
      saveState();
      fetchQuestion(state.index).then(renderQuestion);
    }
  }

  startBtn &&
    startBtn.addEventListener('click', function () {
      state = {
        started: true,
        index: 0,
        total: cfg.totalHint,
        correctKey: [],
        userAnswers: [],
        snapshots: []
      };
      saveState();
      showQuiz();
      fetchQuestion(0).then(renderQuestion);
    });

  primaryBtn.addEventListener('click', onPrimaryClick);

  restartBtn &&
    restartBtn.addEventListener('click', function () {
      try {
        localStorage.removeItem(cfg.storageKey);
      } catch (e) {}
      state = {
        started: false,
        index: 0,
        total: cfg.totalHint || 0,
        correctKey: [],
        userAnswers: [],
        snapshots: []
      };
      saveState();
      showIntro();
    });

  loadState();
  const maxIdx = (state.total || cfg.totalHint) - 1;
  if (state.started && cfg.totalHint > 0 && state.index >= 0 && state.index <= maxIdx) {
    showQuiz();
    fetchQuestion(state.index).then(renderQuestion).catch(showIntro);
  }
})();
</script>
<?php
get_footer();
