import { describe, it, expect } from "vitest";
import { readFileSync } from "node:fs";

const css = readFileSync("src/app/globals.css", "utf8").toLowerCase();

describe("tema Gelawitto", () => {
  it.each([
    ["fresa", "#e8436b"], ["frambuesa", "#b0244e"], ["mora", "#4a1430"], ["durazno", "#ff9e80"],
    ["rubor", "#fde7ec"], ["nata", "#fff9f7"], ["tinta", "#2a2126"],
  ])("define el color %s = %s", (nombre, hex) => {
    expect(css).toContain(`--color-${nombre}: ${hex}`);
  });

  it("el color primario de los componentes es la fresa", () => {
    expect(css).toMatch(/--primary:\s*#e8436b/);
  });
});