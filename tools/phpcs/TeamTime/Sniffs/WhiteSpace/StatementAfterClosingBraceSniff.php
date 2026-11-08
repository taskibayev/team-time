<?php

namespace TeamTime\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class StatementAfterClosingBraceSniff implements Sniff
{
    public function register(): array
    {
        return [T_CLOSE_CURLY_BRACKET];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $next = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if (false === $next || $tokens[$next]['line'] > $tokens[$stackPtr]['line'] + 1) {
            return;
        }

        $allowed = [
            T_ELSE,
            T_ELSEIF,
            T_CATCH,
            T_FINALLY,
            T_WHILE,
            T_SEMICOLON,
            T_COMMA,
            T_CLOSE_CURLY_BRACKET,
            T_CLOSE_PARENTHESIS,
            T_CLOSE_SQUARE_BRACKET,
            T_OBJECT_OPERATOR,
            T_NULLSAFE_OBJECT_OPERATOR,
            T_COMMENT,
        ];

        if (in_array($tokens[$next]['code'], $allowed, true)) {
            return;
        }

        $error = 'A statement after a closing brace must be separated by a blank line';
        if ($phpcsFile->addFixableError($error, $next, 'SameLine')) {
            $lineBreaks = $tokens[$next]['line'] === $tokens[$stackPtr]['line'] ? 2 : 1;
            $phpcsFile->fixer->addContent($stackPtr, str_repeat($phpcsFile->eolChar, $lineBreaks));
        }
    }
}
