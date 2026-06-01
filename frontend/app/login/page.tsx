import { LoginPageClient } from "@/components/auth/login-page-client";

type LoginPageProps = {
  searchParams?: Promise<{ next?: string }>;
};

export default async function LoginPage({ searchParams }: LoginPageProps) {
  const params = searchParams ? await searchParams : undefined;

  return <LoginPageClient nextPath={params?.next} />;
}
