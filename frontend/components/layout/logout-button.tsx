"use client";

import { LoaderCircle, LogOut } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import { Button } from "@/components/ui/button";
import { logoutRequest } from "@/lib/api";

export function LogoutButton() {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();

  async function handleLogout() {
    setError(null);

    try {
      await logoutRequest();
    } catch (logoutError) {
      setError(logoutError instanceof Error ? logoutError.message : "Nao foi possivel terminar a sessao.");
      return;
    }

    startTransition(() => {
      router.replace("/login");
      router.refresh();
    });
  }

  return (
    <div className="flex flex-col items-end gap-2">
      <Button type="button" variant="secondary" onClick={handleLogout} disabled={isPending} className="min-w-36">
        {isPending ? <LoaderCircle className="h-4 w-4 animate-spin" /> : <LogOut className="h-4 w-4" />}
        Terminar sessao
      </Button>
      {error ? <p className="text-right text-xs font-medium text-institutional-red">{error}</p> : null}
    </div>
  );
}