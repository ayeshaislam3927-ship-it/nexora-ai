<?php
/**
 * NEXORA API - Available AI Models List
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$models = [
    [
        'id' => 'gemini-2.5-flash',
        'name' => 'Gemini 2.5 Flash',
        'provider' => 'Google AI',
        'description' => 'Fast, intelligent model optimized for real-time conversation and reasoning.',
        'badge' => 'Recommended',
        'supports_vision' => true
    ],
    [
        'id' => 'gemini-2.5-pro',
        'name' => 'Gemini 2.5 Pro',
        'provider' => 'Google AI',
        'description' => 'Advanced reasoning, deep technical analysis, and complex coding capability.',
        'badge' => 'Pro',
        'supports_vision' => true
    ],
    [
        'id' => 'gpt-4o',
        'name' => 'GPT-4o',
        'provider' => 'OpenAI',
        'description' => 'High-intelligence flagship model for multimodal reasoning and creativity.',
        'badge' => 'Premium',
        'supports_vision' => true
    ],
    [
        'id' => 'claude-3-5-sonnet',
        'name' => 'Claude 3.5 Sonnet',
        'provider' => 'Anthropic',
        'description' => 'Superior code generation, writing, and analytical performance.',
        'badge' => 'Coding Choice',
        'supports_vision' => true
    ],
    [
        'id' => 'grok-2',
        'name' => 'Grok 2',
        'provider' => 'xAI',
        'description' => 'Real-time knowledge and direct, unfiltered analytical responses.',
        'badge' => 'Fast',
        'supports_vision' => false
    ]
];

json_response(['success' => true, 'models' => $models]);
