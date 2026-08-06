Here's the roadmap, same step-by-step approach as before.

## Project: Sign of a Number Checker

**What it does:** user enters a number, PHP tells them if it's Positive, Negative, or Zero.

## Roadmap

**Step 1 — Static HTML shell**
Build the form: one number input, one submit button. No PHP logic yet — just get it rendering.
*Learn:* form structure, single-input forms

**Step 2 — Submit to itself & inspect the data**
Form posts to the same page. Use `var_dump($_POST)` to confirm the value arrives correctly.
*Learn:* `$_POST`, request/response cycle

**Step 3 — Detect submission**
Wrap logic in `if ($_SERVER['REQUEST_METHOD'] === 'POST')` so nothing runs on first load.
*Learn:* conditional execution (same habit as BMI project)

**Step 4 — Validate the input**
Check the field isn't empty and is numeric (`is_numeric()`). This time you'll also decide: should decimals be allowed? Negative numbers with a `-` sign?
*Learn:* input validation, edge-case thinking

**Step 5 — Write the core function**
This is the one real function in the whole project:
```php
function numberSign(float $n): string {
    // logic goes here
}
```
*Learn:* function design, single responsibility

**Step 6 — Handle the logic (three-way branch)**
Positive, Negative, or **Zero** — three outcomes, not two. This is the interesting part of this project: zero is neither positive nor negative, and it's an easy case to forget.
*Learn:* multi-branch conditionals, edge cases

**Step 7 — Display the result safely**
Echo the result back, escaped with `htmlspecialchars()`.
*Learn:* output escaping (still a habit, every time)

**Step 8 — Polish the UI**
- Sticky input value after submit
- Visually differentiate the three outcomes (color, icon, badge) — like the BMI card's colored badge, but simpler since there are only 3 states instead of 4 ranges
*Learn:* conditional styling, UX feedback

---

One thing to decide before we start Step 1: do you want the sign function to treat `0` as its own case, or lump it into "non-negative"? That changes Step 6's logic slightly — most number-sign functions treat it as its own case, and I'd recommend that.

