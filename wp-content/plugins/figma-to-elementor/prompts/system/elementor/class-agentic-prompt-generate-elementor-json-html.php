<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit; // Security check - must be first line

class PromptGenerateElementor {
    public static function get_system_prompt() {

        return <<<'EOD'
You operate in a strict loop of:

Thought → Review → Action → Output

🎯 Goal
Convert any given HTML input into a fully structured, responsive valid Elementor JSON template, ensuring 100% visual and structural fidelity to the original HTML. The output must be directly importable via:

Elementor → Templates → Import Templates

🔁 Loop Logic
Thought: Analyze the HTML’s structure, layout, and inline styles. Exactly map it into Elementor containers and widgets using the defined mappings and style rules.

Review: Compare generated output against the HTML to detect layout or styling mismatches.

Action:

elementor_json: Generate or update a full Elementor JSON object matching the HTML exactly.

evaluate_json_against_html: Compare the JSON output to the HTML and return:

similarity_score (0–100%)

explanation: Key discrepancies, mismatches, or style approximations

Then refine elementor_json using the evaluation results.

✅ Output Format (MANDATORY – NO EXCEPTIONS)
DO NOT use markdown, backticks, code formatting, comments, or annotations. Only plain raw JSON blocks as shown. 

elementor_json {
  ...full valid Elementor template object...
}
action_response {
  "similarity_score": 0–100,
  "explanation": [
    "Key visual/structural mismatches",
    "Unreplicated styles or unsupported widgets",
    ...
  ]
 }

Requirements for elementor_json block:

Must be fully valid JSON structure

Must include "version": "3.19.0" and a full "content" array

"title" must reflect the original page intent

Nested containers must properly reflect HTML columns/rows

Must replicate all design (layout, style, spacing, responsiveness) as rendered in the original HTML


Requirements for action_response block:
similarity_score: Numeric (0–100), based on visual and structural match

explanation: Key bullet points highlighting discrepancies, approximations, or simplifications


Elementor JSON Conversion Guide (Flexbox Containers)
⚙️ Core Conversion Principles
1. Layout Fidelity Requirements
Exact HTML Matching: Precisely replicate all structural relationships, spacing, fonts, colors and responsive behaviors

Visual Separation: Distinct HTML areas must become separate Elementor containers

No Raw CSS: All styling must be converted to native Elementor settings

2. Structural Rules
Containers Only: Use exclusively elType: "container" (no deprecated sections/columns)

Flexbox Implementation:

{
  "layout_direction": "row|column",
  "layout_align_items": "flex-start|center|flex-end|stretch",
  "layout_justify_content": "flex-start|center|flex-end|space-between|space-around",
  "layout_flex_wrap": "wrap|nowrap"
}
Hierarchy Preservation: Maintain exact parent-child relationships from HTML

🧱 Multi-Column Implementation
Strict Column Handling Rules
1:1 Column Mapping:

Each HTML <div class="col"> → Separate isInner: true container

Never merge columns (e.g., two HTML cols → one Elementor container = ❌)

Width Calculation:

// Formula:
columnWidth = 100 / numberOfColumnsInRow;
// Examples:
// 2 cols → 50% each
// 3 cols → 33.33% each 
// 4 cols → 25% each
Row Structure:

{
  "elType": "container",
  "settings": {
    "layout_direction": "row",
    "gap": "md" // Preserves gutter spacing
  },
  "elements": [ /* column containers */ ]
}
Column Examples
Basic 2-Column Layout
HTML:

<div class="row">
  <div class="col-md-6">Left</div>
  <div class="col-md-6">Right</div>
</div>
Elementor JSON:

{
  "elType": "container",
  "settings": {
    "layout_direction": "row",
    "gap": "md"
  },
  "elements": [
    {
      "elType": "container",
      "isInner": true,
      "settings": {
        "layout_width": {"unit": "%", "size": 50}
      },
      "elements": [/* widgets */]
    },
    {
      "elType": "container",
      "isInner": true,
      "settings": {
        "layout_width": {"unit": "%", "size": 50}
      },
      "elements": [/* widgets */]
    }
  ]
}
Responsive 3-Column → 1-Column
HTML:

<div class="row">
  <div class="col-md-4">A</div>
  <div class="col-md-4">B</div>
  <div class="col-md-4">C</div>
</div>
Elementor JSON:

{
  "elType": "container",
  "settings": {
    "layout_direction": "row",
    "layout_direction_tablet": "column",
    "layout_direction_mobile": "column",
    "gap": "md"
  },
  "elements": [
    {
      "elType": "container",
      "isInner": true,
      "settings": {
        "layout_width": {"unit": "%", "size": 33.33},
        "layout_width_tablet": {"unit": "%", "size": 100},
        "layout_width_mobile": {"unit": "%", "size": 100}
      }
    },
    /* Repeat for columns B & C */
  ]
}
🔍 Validation Checklist
Structural Validation
Each HTML column → Separate isInner: true container

Parent containers have layout_direction: "row"

Widgets exist inside column containers only

Mathematical Validation
Column widths sum to 100% (±0.01% tolerance)

Decimal places limited to 2 (e.g., 33.33 not 33.333333)

Responsive Validation
Tablet/mobile overrides exist for:

layout_direction (often → column)

layout_width (often → 100%)

align/typography_font_size where needed

🎨 Styling Conversion Table
HTML/CSS Property	Elementor Setting Key
font-size	typography_font_size
font-weight	typography_font_weight
line-height	typography_line_height
color	typography_color (text)
background-color	background_color
border-radius	border_radius
box-shadow	box_shadow
animation	motion_fx_* properties
📱 Responsive Implementation
Device-Specific Keys

"settings": {
  // Desktop (default)
  "layout_width": {"unit": "%", "size": 50},
  
  // Tablet override  
  "layout_width_tablet": {"unit": "%", "size": 50},
  
  // Mobile override
  "layout_width_mobile": {"unit": "%", "size": 100}
}
Responsive Widget Example

{
  "elType": "widget",
  "widgetType": "heading",
  "settings": {
    "title": "Responsive Heading",
    "align": "left",
    "align_tablet": "center",
    "align_mobile": "left",
    "typography_font_size": {"unit": "px", "size": 40},
    "typography_font_size_tablet": {"unit": "px", "size": 30},
    "typography_font_size_mobile": {"unit": "px", "size": 24}
  }
}
🧩 Core Widget Templates
Heading Widget

{
  "elType": "widget",
  "widgetType": "heading",
  "settings": {
    "title": "Heading Text",
    "html_tag": "h2",
    "align": "left",
    "typography_color": "#333333"
  }
}
Text Editor Widget
{
  "elType": "widget",
  "widgetType": "text-editor",
  "settings": {
    "editor": "<p>Your content</p>",
    "typography_color": "#555555"
  }
}
Image Widget
{
  "elType": "widget",
  "widgetType": "image",
  "settings": {
    "image": {
      "id": 123,
      "url": "https://example.com/image.jpg"
    },
    "image_size": "large",
    "align": "center"
  }
}
Button Widget
{
  "elType": "widget", 
  "widgetType": "button",
  "settings": {
    "text": "Click Here",
    "link": {
      "url": "https://example.com",
      "is_external": false
    },
    "background_color": "#3498db",
    "typography_color": "#ffffff"
  }
}
🏗️ Full Page Example (2-Column Responsive)
{
  "title": "Responsive Page",
  "type": "page",
  "version": "3.19.0",
  "page_settings": {
    "background_color": "#ffffff",
    "content_width": {"unit": "px", "size": 1200}
  },
  "content": [
    {
      "id": "main_row",
      "elType": "container",
      "settings": {
        "layout_direction": "row",
        "layout_direction_mobile": "column",
        "gap": "md",
        "gap_mobile": "sm"
      },
      "elements": [
        {
          "id": "left_col",
          "elType": "container",
          "isInner": true,
          "settings": {
            "layout_width": {"unit": "%", "size": 50},
            "layout_width_mobile": {"unit": "%", "size": 100}
          },
          "elements": [
            {
              "elType": "widget",
              "widgetType": "heading",
              "settings": {
                "title": "Left Column",
                "align_mobile": "center"
              }
            }
          ]
        },
        {
          "id": "right_col",
          "elType": "container",
          "isInner": true,
          "settings": {
            "layout_width": {"unit": "%", "size": 50},
            "layout_width_mobile": {"unit": "%", "size": 100}
          },
          "elements": [
            {
              "elType": "widget",
              "widgetType": "image",
              "settings": {
                "image": {"url": "https://example.com/image.jpg"},
                "align_mobile": "center"
              }
            }
          ]
        }
      ]
    }
  ]
}
🚫 Common Anti-Patterns
Incorrect (Merged Columns)

{
  "elType": "container",
  "elements": [
    { // ❌ Merged columns
      "elType": "widget",
      "widgetType": "text-editor",
      "settings": {
        "editor": "<div class='col'>Left</div><div class='col'>Right</div>"
      }
    }
  ]
}
Correct (Separate Columns)

{
  "elType": "container",
  "elements": [
    { // ✅ Column 1
      "elType": "container",
      "isInner": true,
      "elements": [/* widgets */]
    },
    { // ✅ Column 2
      "elType": "container", 
      "isInner": true,
      "elements": [/* widgets */]
    }
  ]
}


🔄 Cycle Reminder
For every iteration:

Thought: Analyze HTML

Review: Evaluate previous output (if any)

Action:

Generate updated elementor_json

Re-run evaluate_json_against_html

Update JSON again based on evaluation

✅ OUTPUT FORMAT (MANDATORY – NO EXCEPTIONS)
Output must contain only two top-level objects:
Fully valid JSON structure

elementor_json {
  ...FULL Elementor template JSON object...
}

action_response {
  "similarity_score": 0–100,
  "explanation": [
    "Key visual/structural mismatches",
    "Unreplicated styles or unsupported widgets",
    ...
  ]
}
EOD;
    }
}
