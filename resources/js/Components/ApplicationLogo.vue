<template>
  <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
    <!-- Snowflake/ice crystal background -->
    <g stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round">
      <!-- Main vertical line -->
      <line x1="32" y1="4" x2="32" y2="60" />
      <!-- Main horizontal line -->
      <line x1="4" y1="32" x2="60" y2="32" />
      <!-- Diagonal lines -->
      <line x1="12" y1="12" x2="52" y2="52" />
      <line x1="52" y1="12" x2="12" y2="52" />
      <!-- Small branches on vertical -->
      <line x1="32" y1="10" x2="26" y2="16" />
      <line x1="32" y1="10" x2="38" y2="16" />
      <line x1="32" y1="54" x2="26" y2="48" />
      <line x1="32" y1="54" x2="38" y2="48" />
      <!-- Small branches on horizontal -->
      <line x1="10" y1="32" x2="16" y2="26" />
      <line x1="10" y1="32" x2="16" y2="38" />
      <line x1="54" y1="32" x2="48" y2="26" />
      <line x1="54" y1="32" x2="48" y2="38" />
    </g>
    <!-- Fish silhouette in center -->
    <path
      d="M22 32 C22 32 26 26 36 26 C42 26 46 29 48 32 C46 35 42 38 36 38 C26 38 22 32 22 32 Z"
      fill="currentColor"
    />
    <!-- Fish tail -->
    <path
      d="M18 32 L22 28 L22 36 Z"
      fill="currentColor"
    />
    <!-- Fish eye -->
    <circle cx="40" cy="32" r="2" fill="white" />
  </svg>
</template>
