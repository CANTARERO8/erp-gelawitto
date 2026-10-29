import { describe, it, expect } from "vitest";
import { readFileSync } from "node:fs";

const css = readFileSync("resources/css/app.css", "utf8").toLowerCase();
const loginVue = readFileSync("resources/js/Pages/Auth/Login.vue", "utf8");

describe("tema y login Gelawitto ERP", () => {
    it.each([
        ["fresa", "#e8436b"],
        ["frambuesa", "#b0244e"],
        ["mora", "#4a1430"],
        ["durazno", "#ff9e80"],
        ["rubor", "#fde7ec"],
        ["nata", "#fff9f7"],
        ["tinta", "#2a2126"],
    ])("define el color institucional %s = %s en Tailwind CSS v4", (nombre, hex) => {
        expect(css).toContain(`--color-${nombre}: ${hex}`);
    });

    it("define el color primario de los componentes como fresa", () => {
        expect(css).toMatch(/--primary:\s*#e8436b/);
    });

    it("implementa la temática split-screen e control de intentos en Login.vue", () => {
        expect(loginVue).toContain("/images/login-gelawitto.jpg");
        expect(loginVue).toContain("Límite de intentos superados");
        expect(loginVue).toContain("Validando credenciales...");
    });
});
