/**
 * The backend used to INSERT into MySQL. It now serves read-only seed data, so
 * anything submitted through the create forms is kept in sessionStorage and
 * merged into the lists for the current tab only.
 */

const PREFIX = "strata-demo:";

export function readDemoItems<T>(key: string): T[] {
  if (typeof window === "undefined") {
    return [];
  }

  try {
    const stored = window.sessionStorage.getItem(PREFIX + key);
    const parsed = stored ? JSON.parse(stored) : [];
    return Array.isArray(parsed) ? (parsed as T[]) : [];
  } catch {
    return [];
  }
}

export function addDemoItem<T>(key: string, item: T): void {
  if (typeof window === "undefined") {
    return;
  }

  try {
    const items = [...readDemoItems<T>(key), item];
    window.sessionStorage.setItem(PREFIX + key, JSON.stringify(items));
  } catch {
    // sessionStorage can be unavailable or full; the submission is simply lost.
  }
}
