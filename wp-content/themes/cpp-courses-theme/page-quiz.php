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
$ros_link = function_exists('get_field') ? get_field('quiz_rosvgard_link', $page_id) : null;
$ros_url = is_array($ros_link) && !empty($ros_link['url']) ? (string) $ros_link['url'] : '';
$ros_title = is_array($ros_link) && !empty($ros_link['title']) ? (string) $ros_link['title'] : __('Перечень вопросов Росгвардии', 'cpp-courses-theme');
$ros_target = is_array($ros_link) && !empty($ros_link['target']) ? (string) $ros_link['target'] : '_self';

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
    <section class="section section--page-intro">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title"><?php the_title(); ?></h1>
            </div>
        </div>
    </section>

    <section class="section section--test-intro" id="cpp-quiz-intro-section">
        <div class="container">
            <div class="test-intro">
                <div class="test-intro_text entry-content">
                    <?php the_content(); ?>
                </div>
                <div class="test-intro_actions">
                    <?php if ($has_quiz) : ?>
                        <button type="button" class="button button--filled button--lg" id="cpp-quiz-start">
                            <span class="button_text"><?php esc_html_e('Приступить →', 'cpp-courses-theme'); ?></span>
                        </button>
                    <?php else : ?>
                        <p class="page-intro_desc"><?php esc_html_e('Вопросы для теста не выбраны. Укажите их в полях страницы.', 'cpp-courses-theme'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($ros_url !== '') : ?>
                <div class="test-intro_footer">
                    <a class="test-intro_link wave-link" href="<?php echo esc_url($ros_url); ?>" target="<?php echo esc_attr($ros_target); ?>"><?php echo esc_html($ros_title); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($has_quiz) : ?>
        <section class="section section--test-quiz" id="cpp-quiz-play-section" hidden>
            <div class="container">
                <div class="test-quiz">
                    <form class="test-quiz_form" id="cpp-quiz-form" action="#" method="get" onsubmit="return false;">
                        <div class="test-quiz_question" id="cpp-quiz-question-wrap">
                            <div class="test-quiz_question-text entry-content" id="cpp-quiz-question-body"></div>
                        </div>
                        <div class="test-quiz_options" id="cpp-quiz-options"></div>
                        <div class="test-quiz_actions">
                            <span class="test-quiz_progress" id="cpp-quiz-progress" aria-live="polite"></span>
                            <div class="test-quiz_actions-buttons">
                                <button class="button button--filled button--md" type="button" id="cpp-quiz-next">
                                    <span class="button_text"><?php esc_html_e('Далее →', 'cpp-courses-theme'); ?></span>
                                </button>
                                <button class="button button--primary button--md" type="button" id="cpp-quiz-finish" hidden>
                                    <span class="button_text"><?php esc_html_e('Показать результаты', 'cpp-courses-theme'); ?></span>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php if ($ros_url !== '') : ?>
                        <div class="test-quiz_footer">
                            <a class="test-quiz_link wave-link" href="<?php echo esc_url($ros_url); ?>" target="<?php echo esc_attr($ros_target); ?>"><?php echo esc_html($ros_title); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--test-quiz" id="cpp-quiz-results-section" hidden>
            <div class="container">
                <div class="test-quiz">
                    <div class="page-intro" style="margin-bottom: 1.5rem;">
                        <h2 class="page-intro_title" id="cpp-quiz-results-title"><?php echo esc_html($results_title); ?></h2>
                        <?php if ($results_text !== '') : ?>
                            <p class="page-intro_desc"><?php echo nl2br(esc_html($results_text)); ?></p>
                        <?php endif; ?>
                        <p class="page-intro_desc" id="cpp-quiz-results-score"></p>
                    </div>
                    <?php if ($cf7_html !== '') : ?>
                        <div class="test-quiz_form cpp-quiz-results-form">
                            <?php echo $cf7_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($ros_url !== '') : ?>
                        <div class="test-quiz_footer">
                            <a class="test-quiz_link wave-link" href="<?php echo esc_url($ros_url); ?>" target="<?php echo esc_attr($ros_target); ?>"><?php echo esc_html($ros_title); ?></a>
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
    storageKey: 'cpp_quiz_<?php echo (int) $page_id; ?>_v1',
    totalHint: <?php echo (int) count($question_ids); ?>
  };

  const main = document.getElementById('cpp-quiz-main');
  const introSec = document.getElementById('cpp-quiz-intro-section');
  const playSec = document.getElementById('cpp-quiz-play-section');
  const resSec = document.getElementById('cpp-quiz-results-section');
  const startBtn = document.getElementById('cpp-quiz-start');
  const form = document.getElementById('cpp-quiz-form');
  const qBody = document.getElementById('cpp-quiz-question-body');
  const opts = document.getElementById('cpp-quiz-options');
  const nextBtn = document.getElementById('cpp-quiz-next');
  const finishBtn = document.getElementById('cpp-quiz-finish');
  const progressEl = document.getElementById('cpp-quiz-progress');
  const scoreEl = document.getElementById('cpp-quiz-results-score');

  if (!main || !form || !qBody || !opts || !nextBtn || !finishBtn) return;

  let state = {
    started: false,
    index: 0,
    total: cfg.totalHint || 0,
    correctKey: [],
    userAnswers: []
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
          userAnswers: state.userAnswers
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

  function setButtonsForStep() {
    const last = state.total > 0 && state.index >= state.total - 1;
    nextBtn.hidden = last;
    finishBtn.hidden = !last;
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
        const id = name + '_' + i;
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
        const id = name + '_' + i;
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
    setButtonsForStep();
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
    if (introSec) introSec.hidden = false;
    if (playSec) playSec.hidden = true;
    if (resSec) resSec.hidden = true;
  }

  function showPlay() {
    setPagePhase('quiz');
    if (introSec) introSec.hidden = true;
    if (playSec) playSec.hidden = false;
    if (resSec) resSec.hidden = true;
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
    setPagePhase('results');
    if (introSec) introSec.hidden = true;
    if (playSec) playSec.hidden = true;
    if (resSec) resSec.hidden = false;
    try {
      localStorage.removeItem(cfg.storageKey);
    } catch (e) {}
  }

  function goNext() {
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
        userAnswers: []
      };
      saveState();
      showPlay();
      fetchQuestion(0).then(renderQuestion);
    });

  nextBtn.addEventListener('click', function () {
    goNext();
  });

  finishBtn.addEventListener('click', function () {
    const sel = readCurrentSelection();
    state.userAnswers[state.index] = sel;
    saveState();
    showResults();
  });

  loadState();
  const maxIdx = (state.total || cfg.totalHint) - 1;
  if (state.started && cfg.totalHint > 0 && state.index >= 0 && state.index <= maxIdx) {
    showPlay();
    fetchQuestion(state.index).then(renderQuestion).catch(showIntro);
  }
})();
</script>
<?php
get_footer();
