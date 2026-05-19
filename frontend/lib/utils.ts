import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatCurrency(value: number | string, options?: Intl.NumberFormatOptions) {
  const numeric = typeof value === "string" ? Number(value) : value;
  return new Intl.NumberFormat("pt-AO", {
    style: "currency",
    currency: "AOA",
    maximumFractionDigits: 2,
    ...options
  }).format(Number.isFinite(numeric) ? numeric : 0);
}

export function formatCurrencyShort(value: number | string) {
  return formatCurrency(value, {
    maximumFractionDigits: 0
  });
}

export function formatPercent(value: number | string) {
  const numeric = typeof value === "string" ? Number(value) : value;
  return `${Number.isFinite(numeric) ? numeric.toFixed(1) : "0.0"}%`;
}

export function formatDateTime(value?: string) {
  if (!value) {
    return "-";
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat("pt-AO", {
    dateStyle: "medium",
    timeStyle: "short"
  }).format(date);
}

export function formatDate(value?: string) {
  if (!value) {
    return "-";
  }

  const [year, month, day] = value.slice(0, 10).split("-").map(Number);
  const date = year && month && day ? new Date(year, month - 1, day) : new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat("pt-AO", {
    dateStyle: "medium"
  }).format(date);
}
