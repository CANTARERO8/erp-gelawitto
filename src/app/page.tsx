const COLORES = [
  { nombre: "Fresa", clase: "bg-fresa", uso: "Acción principal" },
  { nombre: "Frambuesa", clase: "bg-frambuesa", uso: "Hover y enlaces" },
  { nombre: "Mora", clase: "bg-mora", uso: "Menú lateral" },
  { nombre: "Durazno", clase: "bg-durazno", uso: "Acentos" },
  { nombre: "Rubor", clase: "bg-rubor", uso: "Selección" },
  { nombre: "Nata", clase: "bg-nata border", uso: "Fondo" },
];

export default function Inicio() {
  return (
    <main className="mx-auto max-w-3xl p-8">
      <h1 className="text-3xl text-frambuesa">Paleta Gelawitto</h1>
      <p className="mt-2 text-muted-foreground">Así se ven nuestros colores.</p>
      <div className="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
        {COLORES.map((color) => (
          <div key={color.nombre}>
            <div className={`h-20 rounded-lg ${color.clase}`} />
            <p className="mt-2 font-medium">{color.nombre}</p>
            <p className="text-sm text-muted-foreground">{color.uso}</p>
          </div>
        ))}
      </div>
    </main>
  );
}