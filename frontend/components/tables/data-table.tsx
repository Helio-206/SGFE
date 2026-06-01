"use client";

import {
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  type ColumnDef,
  useReactTable
} from "@tanstack/react-table";
import { ArrowUpDown, ChevronLeft, ChevronRight, Search, SlidersHorizontal, X } from "lucide-react";
import { useId, useMemo, useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { cn } from "@/lib/utils";

type FilterValue = string | number | boolean | Date | null | undefined;

export type DataTableFilter<TData> = {
  id: string;
  label: string;
  type: "select" | "date-range" | "number-range";
  placeholder?: string;
  options?: Array<{ label: string; value: string }>;
  getValue?: (row: TData) => FilterValue;
  minPlaceholder?: string;
  maxPlaceholder?: string;
};

export function DataTable<TData, TValue>({
  columns,
  data,
  searchPlaceholder = "Pesquisar",
  filters = [],
  emptyMessage = "Nenhum registo encontrado."
}: {
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
  searchPlaceholder?: string;
  filters?: DataTableFilter<TData>[];
  emptyMessage?: string;
}) {
  const tableId = useId();
  const [globalFilter, setGlobalFilter] = useState("");
  const [filterValues, setFilterValues] = useState<Record<string, string>>({});

  const filteredData = useMemo(() => {
    if (!filters.length) {
      return data;
    }

    return data.filter((row) =>
      filters.every((filter) => {
        const value = getFilterValue(row, filter);

        if (filter.type === "select") {
          const selected = filterValues[filter.id];
          return !selected || String(value ?? "") === selected;
        }

        if (filter.type === "date-range") {
          const from = filterValues[`${filter.id}:from`];
          const to = filterValues[`${filter.id}:to`];
          if (!from && !to) {
            return true;
          }
          const rowDate = toDateOnly(value);
          if (!rowDate) {
            return false;
          }
          return (!from || rowDate >= toDateOnly(from)!) && (!to || rowDate <= toDateOnly(to)!);
        }

        const min = filterValues[`${filter.id}:min`];
        const max = filterValues[`${filter.id}:max`];
        if (!min && !max) {
          return true;
        }
        const numberValue = Number(value);
        if (!Number.isFinite(numberValue)) {
          return false;
        }
        return (!min || numberValue >= Number(min)) && (!max || numberValue <= Number(max));
      })
    );
  }, [data, filters, filterValues]);

  const table = useReactTable({
    data: filteredData,
    columns,
    state: { globalFilter },
    onGlobalFilterChange: setGlobalFilter,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getPaginationRowModel: getPaginationRowModel()
  });

  const hasActiveFilters = globalFilter.trim().length > 0 || Object.values(filterValues).some(Boolean);
  const rows = table.getRowModel().rows;

  function updateFilter(key: string, value: string) {
    setFilterValues((current) => ({ ...current, [key]: value }));
    table.setPageIndex(0);
  }

  function resetFilters() {
    setGlobalFilter("");
    setFilterValues({});
    table.setPageIndex(0);
  }

  return (
    <div className="space-y-4">
      <div className="rounded-lg border border-border/80 bg-surface/95 p-4 shadow-quiet backdrop-blur-sm">
        <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div className="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-[minmax(260px,1.35fr)_repeat(3,minmax(170px,1fr))]">
            <div className="space-y-2">
              <Label htmlFor={`${tableId}-search`} className="text-xs uppercase text-muted-foreground">
                Pesquisa
              </Label>
              <div className="relative">
                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                  id={`${tableId}-search`}
                  value={globalFilter}
                  onChange={(event) => {
                    setGlobalFilter(event.target.value);
                    table.setPageIndex(0);
                  }}
                  placeholder={searchPlaceholder}
                  className="pl-9"
                />
              </div>
            </div>
            {filters.map((filter) => (
              <FilterControl key={filter.id} tableId={tableId} filter={filter} values={filterValues} onChange={updateFilter} />
            ))}
          </div>
          <div className="flex items-center justify-between gap-3 xl:flex-col xl:items-end">
            <div className="flex items-center gap-2 text-xs font-medium text-muted-foreground">
              <SlidersHorizontal className="h-4 w-4" />
              {table.getFilteredRowModel().rows.length} de {data.length} registos
            </div>
            {hasActiveFilters ? (
              <Button type="button" variant="ghost" size="sm" onClick={resetFilters}>
                <X className="h-4 w-4" />
                Limpar
              </Button>
            ) : null}
          </div>
        </div>
      </div>
      <div className="overflow-hidden rounded-lg border border-border/80 bg-surface/95 shadow-quiet">
        <div className="overflow-x-auto">
        <table className="min-w-[860px] text-sm">
          <thead className="bg-surface-muted/80">
            {table.getHeaderGroups().map((headerGroup) => (
              <tr key={headerGroup.id}>
                {headerGroup.headers.map((header) => (
                  <th key={header.id} className="px-4 py-3 text-left text-xs font-bold uppercase text-muted-foreground">
                    {header.isPlaceholder ? null : (
                      <button
                        type="button"
                        className={cn(
                          "inline-flex items-center gap-2 text-left",
                          header.column.getCanSort() ? "transition hover:text-institutional-blue" : "cursor-default"
                        )}
                        onClick={header.column.getToggleSortingHandler()}
                        disabled={!header.column.getCanSort()}
                        title={header.column.getCanSort() ? "Ordenar coluna" : undefined}
                      >
                        {flexRender(header.column.columnDef.header, header.getContext())}
                        {header.column.getCanSort() ? <ArrowUpDown className="h-3.5 w-3.5" /> : null}
                      </button>
                    )}
                  </th>
                ))}
              </tr>
            ))}
          </thead>
          <tbody className="divide-y divide-border/75">
            {rows.length ? (
              rows.map((row) => (
                <tr key={row.id} className="transition-colors hover:bg-surface-muted/50">
                  {row.getVisibleCells().map((cell) => (
                    <td key={cell.id} className="px-4 py-3 align-middle text-institutional-ink">
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </td>
                  ))}
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={table.getAllLeafColumns().length} className="px-4 py-10 text-center text-sm font-medium text-muted-foreground">
                  {emptyMessage}
                </td>
              </tr>
            )}
          </tbody>
        </table>
        </div>
      </div>
      <div className="flex flex-col gap-3 rounded-lg border border-border/80 bg-surface/95 px-4 py-3 text-sm text-muted-foreground shadow-quiet sm:flex-row sm:items-center sm:justify-between">
        <span>
          Pagina {table.getState().pagination.pageIndex + 1} de {Math.max(table.getPageCount(), 1)}
        </span>
        <div className="flex items-center justify-end gap-2">
          <Button variant="secondary" size="sm" onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()} title="Pagina anterior">
            <ChevronLeft className="h-4 w-4" />
            Anterior
          </Button>
          <Button variant="secondary" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()} title="Pagina seguinte">
            Seguinte
            <ChevronRight className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  );
}

function FilterControl<TData>({
  filter,
  tableId,
  values,
  onChange
}: {
  filter: DataTableFilter<TData>;
  tableId: string;
  values: Record<string, string>;
  onChange: (key: string, value: string) => void;
}) {
  if (filter.type === "select") {
    return (
      <div className="space-y-2">
        <Label htmlFor={`${tableId}-filter-${filter.id}`} className="text-xs uppercase text-muted-foreground">
          {filter.label}
        </Label>
        <select
          id={`${tableId}-filter-${filter.id}`}
          value={values[filter.id] ?? ""}
          onChange={(event) => onChange(filter.id, event.target.value)}
          className="focus-ring h-10 w-full rounded-md border border-input bg-surface-strong/90 px-3 text-sm text-institutional-ink shadow-line"
        >
          <option value="">{filter.placeholder ?? "Todos"}</option>
          {(filter.options ?? []).map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>
    );
  }

  if (filter.type === "date-range") {
    return (
      <div className="space-y-2">
        <Label className="text-xs uppercase text-muted-foreground">{filter.label}</Label>
        <div className="grid grid-cols-2 gap-2">
          <Input
            type="date"
            aria-label={`${filter.label} inicio`}
            value={values[`${filter.id}:from`] ?? ""}
            onChange={(event) => onChange(`${filter.id}:from`, event.target.value)}
          />
          <Input
            type="date"
            aria-label={`${filter.label} fim`}
            value={values[`${filter.id}:to`] ?? ""}
            onChange={(event) => onChange(`${filter.id}:to`, event.target.value)}
          />
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-2">
      <Label className="text-xs uppercase text-muted-foreground">{filter.label}</Label>
      <div className="grid grid-cols-2 gap-2">
        <Input
          type="number"
          inputMode="decimal"
          placeholder={filter.minPlaceholder ?? "Min."}
          value={values[`${filter.id}:min`] ?? ""}
          onChange={(event) => onChange(`${filter.id}:min`, event.target.value)}
        />
        <Input
          type="number"
          inputMode="decimal"
          placeholder={filter.maxPlaceholder ?? "Max."}
          value={values[`${filter.id}:max`] ?? ""}
          onChange={(event) => onChange(`${filter.id}:max`, event.target.value)}
        />
      </div>
    </div>
  );
}

function getFilterValue<TData>(row: TData, filter: DataTableFilter<TData>) {
  if (filter.getValue) {
    return filter.getValue(row);
  }

  return (row as Record<string, FilterValue>)[filter.id];
}

function toDateOnly(value: FilterValue) {
  if (!value) {
    return null;
  }

  if (value instanceof Date) {
    return new Date(value.getFullYear(), value.getMonth(), value.getDate()).getTime();
  }

  const text = String(value).slice(0, 10);
  const [year, month, day] = text.split("-").map(Number);
  if (!year || !month || !day) {
    return null;
  }

  return new Date(year, month - 1, day).getTime();
}
