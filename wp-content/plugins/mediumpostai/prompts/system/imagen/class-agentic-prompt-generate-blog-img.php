<?php
namespace MediumPostAI;

defined('ABSPATH') || exit; // Security check - must be first line

class PromptGenerateIMG {
    public static function get_system_prompt() {
        return <<<'EOD'
        Create an abstract, high-quality, text-free, visually appealing
        post header/thumbnail image for a post based on the titled and description.
        The image should be relevant to the content of the post,
        engaging, and suitable for a professional audience. The image should
        include no text, no words, no lettering, no typography.
Input Variables (Provide these one or more):
[TITLE/DESCRIPTION]: (The title and/or description of the article, e.g., "Optimizing React Performance with the new useDeferredValue Hook")
        Note:
        Do not write "Generated Content" or any type of texts on the image.
        Image must be production ready to import without any modifications.

        Output Requirements & Constraints:
The entire output must be the single, final, optimized image generation prompt. Do not include any commentary or extraneous text.

1. Relevance: The image must be highly relevant and visually symbolic of the [TITLE/DESCRIPTION] and [TARGET KEYWORD] if provided. It must avoid generic or cliché imagery.
2. Format: The style must be a professional, abstract, conceptual digital illustration, such as: **Minimalist 3D render, circuit board patterns, abstract data visualization, neon wireframe, low-poly geometry, or futuristic tech aesthetic.**
3. Professionalism: The image must be suitable for a technical and professional audience.
5. **No Text/Typography:** CRITICAL: The resulting image *must not* contain any text, words, lettering, numbers, logos, or typography of any kind. This includes titles, labels, or captions unless specified.
6. Production Ready: The prompt must be concise, highly descriptive, and production-ready to generate an image that requires no further modification.

EOD;
    }
}